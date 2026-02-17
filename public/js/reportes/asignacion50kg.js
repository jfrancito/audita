$(document).ready(function () {
    App.init();
    $(".select2").select2({
        width: '100%'
    });

    $('.datetimepicker').datetimepicker({
        autoclose: true,
        pickerPosition: "bottom-left",
        componentIcon: '.icon-th',
        navIcons: {
            rightIcon: 'mdi mdi-chevron-right',
            leftIcon: 'mdi mdi-chevron-left'
        },
        linkFormat: 'yyyy-mm-dd'
    });



    $("#btnBuscar").click(function () {
        var fechainicio = $("#fechainicio").val();
        var fechafin = $("#fechafin").val();
        var idopcion = $("#idopcion").val();
        var _token = $("#token").val();

        if (fechainicio == "") {
            alerterrorajax("Seleccione una fecha de inicio");
            return false;
        }
        if (fechafin == "") {
            alerterrorajax("Seleccione una fecha de fin");
            return false;
        }

        abrircargando();

        $.ajax({
            type: "POST",
            url: $('#carpeta').val() + "/ajax-listar-asignacion-50kg",
            data: {
                _token: _token,
                fechainicio: fechainicio,
                fechafin: fechafin,
                idopcion: idopcion
            },
            success: function (data) {
                cerrarcargando();
                $(".ajax_lista_asignacion_50kg").html(data);
            },
            error: function (data) {
                cerrarcargando();
                if (data.status == 500) {
                    var error = "Ocurrio un error en el servidor";
                    alerterrorajax(error);
                } else {
                    var error = data.responseText;
                    alerterrorajax(error);
                }
            }
        });
    });

    $("#btnExcel").click(function () {
        var fechainicio = $("#fechainicio").val();
        var fechafin = $("#fechafin").val();
        var idopcion = $("#idopcion").val();
        var _token = $("#token").val();

        if (fechainicio == "") {
            alerterrorajax("Seleccione una fecha de inicio");
            return false;
        }
        if (fechafin == "") {
            alerterrorajax("Seleccione una fecha de fin");
            return false;
        }

        // Limpiar cookie previa por si acaso
        eraseCookie('download_started');

        abrircargando();

        var url = $("#carpeta").val() + "/exportar-excel-asignacion-50kg";

        // Crear un formulario temporal para enviar el POST
        var form = $('<form action="' + url + '" method="post">' +
            '<input type="hidden" name="_token" value="' + _token + '" />' +
            '<input type="hidden" name="fechainicio" value="' + fechainicio + '" />' +
            '<input type="hidden" name="fechafin" value="' + fechafin + '" />' +
            '<input type="hidden" name="idopcion" value="' + idopcion + '" />' +
            '</form>');
        $('body').append(form);
        form.submit();
        form.remove();

        // Controlar el cierre del cargando con la cookie
        // Controlar el cierre del cargando con la cookie
        var checkDownload = setInterval(function () {
            var cookieValue = getCookie('download_started');
            if (cookieValue == 'true') {
                cerrarcargando();
                eraseCookie('download_started');
                clearInterval(checkDownload);
            }
        }, 1000); // Check every 1 second to reduce browser load
    });

    function getCookie(name) {
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    }

    function eraseCookie(name) {
        document.cookie = name + '=; Max-Age=-99999999; path=/;';
    }

});