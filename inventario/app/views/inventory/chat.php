<?php
// app/views/inventory/chat.php
?>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/facturacion/facturacion/config/sidebar3.php'; 
include "../../facturacion/config/boton_volver.php";?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat de Soporte IA</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <link rel="stylesheet" type="text/css" href="/facturacion/estilos/estilos.css">

    <script src="https://cdn.platform.openai.com/deployments/chatkit/chatkit.js"></script>

    <style>
        :root {
            --header-h: 76px;
            --helper-h: 52px;
            --page-gap: 16px;
            --chat-radius: 16px;
        }

        html,
        body {
            height: 100%;
            margin: 0;
            font-family: 'Inter', sans-serif;
            background-color: #f4f6f9;
            overflow-x: hidden;
        }

        body {
            min-height: 100vh;
        }

        .page-chat {
            min-height: 100vh;
            padding: 12px;
        }

        .chat-wrapper {
            width: 100%;
            max-width: 700px; /* reducido */
            margin: 0 auto;
        }

        .chat-card {
            border: none;
            border-radius: var(--chat-radius);
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            background: #fff;

            /* ALTURA AJUSTADA */
            height: 75vh;
            max-height: 75vh;
            min-height: 500px;

            display: flex;
            flex-direction: column;
        }

        .chat-header {
            background: linear-gradient(90deg, #007bff, #0056b3);
            color: #fff;
            padding: 14px 18px;
            flex-shrink: 0;
        }

        .chat-header h4 {
            margin: 0;
            font-size: 1.15rem;
            font-weight: 600;
        }

        .chat-header p {
            margin: 4px 0 0;
            font-size: 0.9rem;
            opacity: 0.92;
        }

        .chat-host {
            flex: 1 1 auto;
            min-height: 0;
            background: #fff;
            overflow: hidden;
        }

        #chat-mount,
        openai-chatkit {
            display: block;
            width: 100%;
            height: 100%;
            min-height: 100%;
        }

        .helper-text {
            font-size: 0.84rem;
            color: #6c757d;
            padding: 10px 18px;
            border-top: 1px solid #e9ecef;
            background: #fff;
            flex-shrink: 0;
        }

        .chat-error {
            padding: 20px;
            color: #b02a37;
            font-size: 14px;
            white-space: pre-wrap;
        }

        /* Tablets */
        @media (max-width: 992px) {
            .page-chat {
                padding: 10px;
            }

            .chat-card {
                height: 70vh;
                max-height: 70vh;
                border-radius: 14px;
            }

            .chat-header {
                padding: 12px 16px;
            }

            .chat-header h4 {
                font-size: 1.05rem;
            }

            .chat-header p {
                font-size: 0.85rem;
            }

            .helper-text {
                padding: 9px 16px;
                font-size: 0.8rem;
            }
        }

        /* Laptops pequeñas / pantallas bajas */
        @media (max-height: 800px) {
            .chat-card {
                height: 65vh;
                max-height: 65vh;
            }

            .chat-header {
                padding: 10px 14px;
            }

            .chat-header h4 {
                font-size: 1rem;
            }

            .chat-header p {
                font-size: 0.82rem;
                margin-top: 2px;
            }

            .helper-text {
                padding: 8px 14px;
                font-size: 0.78rem;
            }
        }

        /* Móviles */
        @media (max-width: 576px) {
            .page-chat {
                padding: 0;
            }

            .container,
            .container-fluid {
                padding-left: 0 !important;
                padding-right: 0 !important;
            }

            .chat-wrapper {
                max-width: 100%;
            }

            .chat-card {
                height: 85vh;
                max-height: 85vh;
                min-height: 85vh;
                border-radius: 0;
            }

            .chat-header {
                padding: 12px 14px;
            }

            .chat-header h4 {
                font-size: 1rem;
            }

            .chat-header p {
                font-size: 0.8rem;
            }

            .helper-text {
                padding: 8px 12px;
                font-size: 0.77rem;
            }
        }
    </style>
</head>

<body>

    <div class="page-chat">
        <div class="container-fluid">
            <div class="chat-wrapper">
                <div class="card chat-card">
                    <div class="chat-header">
                        <h4>💬 Chat de Soporte IA</h4>
                        <p>Asistente interno conectado a tu agente de OpenAI.</p>
                    </div>

                    <div class="chat-host">
                        <div id="chat-mount"></div>
                    </div>

                    <div class="helper-text">
                        Chat interno de soporte para inventario y ayuda operativa.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const mount = document.getElementById('chat-mount');

            function showError(message) {
                console.error(message);
                if (mount) {
                    mount.innerHTML = '<div class="chat-error">' + String(message) + '</div>';
                }
            }

            async function fetchClientSecret() {
                const response = await fetch('index.php?action=chat_session', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                const text = await response.text();

                let data = {};
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    throw new Error('chat_session no devolvió JSON válido. Respuesta: ' + text);
                }

                if (!response.ok) {
                    throw new Error(data.error || 'Error HTTP ' + response.status);
                }

                if (!data.client_secret) {
                    throw new Error('No llegó client_secret. Respuesta: ' + text);
                }

                return data.client_secret;
            }

            async function initChatKit() {
                try {
                    if (!mount) {
                        throw new Error('No existe #chat-mount');
                    }

                    let attempts = 0;
                    while (!customElements.get('openai-chatkit')) {
                        attempts++;
                        if (attempts > 100) {
                            throw new Error('El custom element openai-chatkit no quedó registrado.');
                        }
                        await new Promise(resolve => setTimeout(resolve, 100));
                    }

                    const el = document.createElement('openai-chatkit');

                    if (typeof el.setOptions !== 'function') {
                        throw new Error('openai-chatkit existe pero no expone setOptions().');
                    }

                    mount.innerHTML = '';
                    mount.appendChild(el);

                    el.setOptions({
                        api: {
                            async getClientSecret() {
                                return await fetchClientSecret();
                            }
                        },
                        theme: {
                            colorScheme: 'light',
                            radius: 'round',
                            density: 'compact',
                            color: {
                                accent: {
                                    primary: '#007bff',
                                    level: 2
                                }
                            }
                        },
                        composer: {
                            placeholder: 'Escribe tu consulta de soporte...'
                        },
                        startScreen: {
                            greeting: 'Hola, soy tu asistente de soporte. ¿En qué te ayudo hoy?',
                            prompts: [
                                {
                                    label: 'Consultar inventario',
                                    prompt: 'Ayúdame a consultar inventario.'
                                },
                                {
                                    label: 'Registrar incidencia',
                                    prompt: 'Necesito reportar una incidencia de soporte.'
                                },
                                {
                                    label: 'Buscar equipo',
                                    prompt: 'Quiero buscar un equipo por serial o marquilla.'
                                }
                            ]
                        }
                    });
                } catch (error) {
                    showError('Error cargando el chat: ' + (error.message || error));
                }
            }

            window.addEventListener('load', initChatKit);
        })();
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>