package bo.ibc.ibc_movilidad

import io.flutter.embedding.android.FlutterActivity
import io.flutter.embedding.engine.FlutterEngine
import io.flutter.plugin.common.MethodChannel
import android.security.keystore.KeyGenParameterSpec
import android.security.keystore.KeyProperties
import android.util.Base64
import java.security.KeyStore
import javax.crypto.Cipher
import javax.crypto.KeyGenerator
import javax.crypto.SecretKey
import javax.crypto.spec.GCMParameterSpec
import android.view.KeyEvent
import android.content.Intent
import android.speech.RecognizerIntent

class MainActivity : FlutterActivity() {
    private var canalSesion: MethodChannel? = null
    private var atajoVolumen = false
    override fun dispatchKeyEvent(event: KeyEvent): Boolean {
        if (atajoVolumen && hasWindowFocus() && vozPendiente == null && event.keyCode == KeyEvent.KEYCODE_VOLUME_UP) {
            // Una pulsación corta; no capturar combinaciones ni pulsaciones largas de TalkBack.
            if (event.action == KeyEvent.ACTION_UP && !event.isCanceled && event.eventTime - event.downTime < 500) {
                canalSesion?.invokeMethod("volumeUp", null)
            }
        }
        // Mantener volumen y accesibilidad del sistema operativos.
        return super.dispatchKeyEvent(event)
    }
    private var vozPendiente: MethodChannel.Result? = null
    override fun onActivityResult(requestCode: Int, resultCode: Int, data: Intent?) {
        super.onActivityResult(requestCode, resultCode, data)
        if(requestCode == 8041) {
            vozPendiente?.success(if(resultCode == RESULT_OK) data?.getStringArrayListExtra(RecognizerIntent.EXTRA_RESULTS)?.firstOrNull() else null)
            vozPendiente = null
        }
    }
    private val alias = "ibc_movilidad_session_v1"
    private fun key(): SecretKey {
        val store = KeyStore.getInstance("AndroidKeyStore").apply { load(null) }
        (store.getKey(alias, null) as? SecretKey)?.let { return it }
        return KeyGenerator.getInstance(KeyProperties.KEY_ALGORITHM_AES, "AndroidKeyStore").apply {
            init(KeyGenParameterSpec.Builder(alias, KeyProperties.PURPOSE_ENCRYPT or KeyProperties.PURPOSE_DECRYPT)
                .setBlockModes(KeyProperties.BLOCK_MODE_GCM).setEncryptionPaddings(KeyProperties.ENCRYPTION_PADDING_NONE).build())
        }.generateKey()
    }
    override fun configureFlutterEngine(engine: FlutterEngine) {
        super.configureFlutterEngine(engine)
        canalSesion = MethodChannel(engine.dartExecutor.binaryMessenger, "bo.ibc/session")
        canalSesion!!.setMethodCallHandler { call, result ->
            val local = call.method.endsWith("Local")
            val prefs = getSharedPreferences(if(local) alias+"_local" else alias, MODE_PRIVATE)
            try {
                when (call.method) {
                    "volumeShortcut" -> { atajoVolumen = call.arguments == true; result.success(null) }
                    "voice" -> {
                        check(vozPendiente == null) { "Reconocimiento ya activo" }
                        vozPendiente=result
                        try {
                            startActivityForResult(Intent(RecognizerIntent.ACTION_RECOGNIZE_SPEECH).apply {
                                putExtra(RecognizerIntent.EXTRA_LANGUAGE_MODEL, RecognizerIntent.LANGUAGE_MODEL_FREE_FORM)
                                putExtra(RecognizerIntent.EXTRA_LANGUAGE,"es-BO")
                                putExtra(RecognizerIntent.EXTRA_PROMPT,call.arguments as? String ?: "Escuchando")
                            },8041)
                        } catch(e: Exception) { vozPendiente=null;result.error("VOICE_UNAVAILABLE","Reconocimiento de voz no disponible. Revisa el servicio de voz de Android.",null) }
                    }
                    "save", "saveLocal" -> {
                        val cipher = Cipher.getInstance("AES/GCM/NoPadding").apply { init(Cipher.ENCRYPT_MODE, key()) }
                        val encrypted = cipher.doFinal((call.arguments as String).toByteArray(Charsets.UTF_8))
                        val saved = prefs.edit().putString("iv", Base64.encodeToString(cipher.iv, Base64.NO_WRAP))
                            .putString("value", Base64.encodeToString(encrypted, Base64.NO_WRAP)).commit()
                        check(saved) { "No se pudo guardar la sesión" }; result.success(null)
                    }
                    "read", "readLocal" -> {
                        val value = prefs.getString("value", null)
                        if (value == null) result.success(null) else {
                            val iv = Base64.decode(prefs.getString("iv", ""), Base64.NO_WRAP)
                            val cipher = Cipher.getInstance("AES/GCM/NoPadding").apply { init(Cipher.DECRYPT_MODE, key(), GCMParameterSpec(128, iv)) }
                            result.success(String(cipher.doFinal(Base64.decode(value, Base64.NO_WRAP)), Charsets.UTF_8))
                        }
                    }
                    "clear", "clearLocal" -> { check(prefs.edit().clear().commit()); result.success(null) }
                    else -> result.notImplemented()
                }
            } catch (e: Exception) {
                if (call.method == "read" || call.method == "readLocal") prefs.edit().clear().commit()
                result.error("SESSION_STORAGE", "No se pudo recuperar o guardar la sesión segura", null)
            }
        }
    }
}
