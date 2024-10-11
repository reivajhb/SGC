$(document).ready(function () {
    cargarMensajes();
    setInterval(cargarMensajes, 5000); // Actualiza los mensajes cada 5 segundos

    $('#mensaje').on('keydown', function (e) {
        if (e.key === 'Enter') {
            enviarMensaje();
        }
    });
});

function cargarMensajes() {
    $.ajax({
        url: 'recuperar_mensajes.php',
        type: 'GET',
        success: function (data) {
            try {
                var response = JSON.parse(data);

                if (response.success) {
                    var mensajes = response.mensajes;
                    var chatMessages = $('#chat-messages');
                    chatMessages.empty();

                    mensajes.forEach(function (mensaje) {
                        var mensajeHTML = `<p><strong>${mensaje.usuario}:</strong> ${mensaje.mensaje}</p>`;
                        chatMessages.append(mensajeHTML);
                    });

                    chatMessages.scrollTop(chatMessages[0].scrollHeight);
                } else {
                    console.error('Error al cargar mensajes:', response.error);
                }
            } catch (error) {
                console.error('Error al parsear la respuesta JSON:', error);
            }
        },
        error: function (error) {
            console.error('Error al cargar mensajes:', error);
        }
    });
}

function enviarMensaje() {
    var mensaje = $('#mensaje').val();

    if (mensaje.trim() !== '') {
        $.ajax({
            url: 'insertar_mensaje.php',
            type: 'POST',
            data: { mensaje: mensaje },
            success: function () {
                cargarMensajes();
                $('#mensaje').val('');
            }
        });
    }
}