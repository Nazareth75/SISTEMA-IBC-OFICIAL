<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Declaración Jurada - Tutor Legal - IBC</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cinzel:wght@500;700&family=Montserrat:wght@400;600;700&display=swap');
        
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 40px;
            color: #1f2937;
        }

        .document-container {
            background-color: #ffffff;
            max-width: 800px;
            margin: 0 auto;
            padding: 60px 80px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            position: relative;
        }

        /* Marca de agua */
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-family: 'Cinzel', serif;
            font-size: 80px;
            color: rgba(0, 104, 55, 0.03);
            font-weight: 700;
            pointer-events: none;
            white-space: nowrap;
            user-select: none;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #006837;
            padding-bottom: 20px;
            margin-bottom: 40px;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo-box {
            width: 50px;
            height: 50px;
            background-color: #006837;
            color: #ffffff;
            font-family: 'Cinzel', serif;
            font-weight: 700;
            font-size: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
        }

        .logo-text h1 {
            font-family: 'Cinzel', serif;
            font-size: 18px;
            color: #004d27;
            margin: 0;
            font-weight: 700;
            letter-spacing: 1px;
        }

        .logo-text p {
            font-size: 11px;
            color: #6b7280;
            margin: 2px 0 0 0;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .doc-code {
            text-align: right;
            font-size: 12px;
            font-weight: 700;
            color: #006837;
            font-family: monospace;
            background-color: #e8f7ef;
            padding: 6px 12px;
            border-radius: 6px;
        }

        .title {
            text-align: center;
            font-family: 'Cinzel', serif;
            font-size: 20px;
            color: #111827;
            margin-bottom: 30px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .content {
            font-size: 14px;
            line-height: 1.8;
            text-align: justify;
            color: #374151;
        }

        .field-label {
            font-weight: 700;
            color: #111827;
        }

        .section-title {
            font-family: 'Cinzel', serif;
            font-size: 14px;
            font-weight: 700;
            color: #004d27;
            border-bottom: 1px dashed #d1d5db;
            padding-bottom: 5px;
            margin-top: 30px;
            margin-bottom: 15px;
            text-transform: uppercase;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
            margin-bottom: 30px;
            background-color: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #f3f4f6;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            border-bottom: 1px solid #f3f4f6;
            padding-bottom: 6px;
        }

        .signatures {
            margin-top: 60px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #9ca3af;
            margin-top: 50px;
            padding-top: 10px;
            font-size: 12px;
            font-weight: 600;
            color: #4b5563;
        }

        .actions-bar {
            max-width: 800px;
            margin: 20px auto;
            display: flex;
            justify-content: flex-end;
            gap: 15px;
        }

        .btn {
            font-family: 'Montserrat', sans-serif;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-print {
            background-color: #006837;
            color: #ffffff;
            box-shadow: 0 4px 6px -1px rgba(0, 104, 55, 0.2);
        }

        .btn-print:hover {
            background-color: #004d27;
            transform: translateY(-1px);
        }

        .btn-back {
            background-color: #e5e7eb;
            color: #374151;
        }

        .btn-back:hover {
            background-color: #d1d5db;
        }

        @media print {
            body {
                background-color: #ffffff;
                margin: 0;
                padding: 0;
            }

            .document-container {
                box-shadow: none;
                border: none;
                padding: 20px 40px;
                max-width: 100%;
            }

            .actions-bar {
                display: none;
            }
        }
    </style>
</head>
<body>

    <div class="actions-bar">
        <a href="{{ route('personas-no-videntes.tutores.index', $persona) }}" class="btn btn-back">
            Volver
        </a>
        <button onclick="window.print()" class="btn btn-print">
            Imprimir Declaración
        </button>
    </div>

    <div class="document-container">
        <div class="watermark">IBC BOLIVIA</div>

        <div class="header">
            <div class="logo-section">
                <div class="logo-box">IBC</div>
                <div class="logo-text">
                    <h1>Instituto Boliviano de la Ceguera</h1>
                    <p>Departamento de Registro y Tutelas</p>
                </div>
            </div>
            <div class="doc-code">
                DEC-TUT-{{ str_pad($persona->cod_personas_no_videntes, 4, '0', STR_PAD_LEFT) }}
            </div>
        </div>

        <div class="title">
            Declaración de Designación de Tutor Legal
        </div>

        <div class="content">
            <p>
                Por medio del presente documento, y ante las autoridades correspondientes del 
                <strong>Instituto Boliviano de la Ceguera (IBC)</strong>, se deja constancia formal de la 
                designación del tutor legal responsable de la persona no vidente registrada en el sistema.
            </p>

            <div class="section-title">Datos del Afiliado / Persona No Vidente</div>
            <div class="info-grid">
                <div class="info-item">
                    <span class="field-label">Nombre Completo:</span>
                    <span>{{ $persona->nom_pnv }} {{ $persona->ape_pnv }}</span>
                </div>
                <div class="info-item">
                    <span class="field-label">Cédula de Identidad:</span>
                    <span>{{ $persona->cii_pnv }} {{ $persona->exp_pnv }}</span>
                </div>
                <div class="info-item">
                    <span class="field-label">Código Único IBC:</span>
                    <span>{{ $persona->ide_pnv ?? 'PNV-'.$persona->cod_personas_no_videntes }}</span>
                </div>
            </div>

            <div class="section-title">Datos del Tutor Legal Declarado</div>
            <div class="info-grid">
                <div class="info-item">
                    <span class="field-label">Nombre Completo:</span>
                    <span>{{ $tutor ? $tutor->nombre_completo : '________________________________________________' }}</span>
                </div>
                <div class="info-item">
                    <span class="field-label">Parentesco o Relación:</span>
                    <span>{{ $tutor ? $tutor->par_tco : '________________________________________________' }}</span>
                </div>
                <div class="info-item">
                    <span class="field-label">Número de Teléfono:</span>
                    <span>{{ $tutor ? $tutor->tel_tco : '________________________________________________' }}</span>
                </div>
                <div class="info-item">
                    <span class="field-label">Correo Electrónico:</span>
                    <span>{{ $tutor ? ($tutor->cor_tco ?? 'No registrado') : '________________________________________________' }}</span>
                </div>
            </div>

            <p style="margin-top: 30px;">
                El suscrito tutor declara bajo juramento asumir la representación legal y acompañamiento 
                de la persona no vidente anteriormente descrita para todas las gestiones administrativas, 
                beneficios sociales, accesos a salud y capacitaciones provistas por el IBC. Asimismo, se 
                compromete a notificar de inmediato cualquier cambio de domicilio, estado de salud o datos de 
                contacto a la institución.
            </p>

            <p style="margin-top: 20px;">
                Para fines de validez y constancia legal de la tutoría, se adjunta copia de Cédula de Identidad 
                del tutor y la correspondiente documentación/resolución legal que avala esta tutoría.
            </p>

            <p style="text-align: right; margin-top: 40px; font-size: 13px;">
                Fecha de Emisión: <strong>{{ date('d/m/Y') }}</strong>
            </p>

            <div class="signatures">
                <div>
                    <div class="signature-line">
                        Firma del Tutor Legal
                    </div>
                </div>
                <div>
                    <div class="signature-line">
                        Autorizado por: IBC Bolivia
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
