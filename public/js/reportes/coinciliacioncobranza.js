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

    $('#cod_zona').on('change', function () {
        var cod_zona = $(this).val();
        var _token = $('#token').val();
        var $target = $('.ajax_jefe_venta');
        var $target_cliente = $('.ajax_cliente');

        // Efecto de carga
        abrircargando();
        $target_cliente.html('<div class="form-group"><label>Cliente:</label><select class="form-control select2" disabled><option value="-1">Seleccionar</option></select></div>');

        $.ajax({
            type: 'POST',
            url: $('#carpeta').val() + '/ajax-listar-jefes-ventas',
            data: {
                cod_zona: cod_zona,
                _token: _token
            },
            success: function (data) {
                cerrarcargando();
                $target.html(data);
            },
            error: function (data) {
                cerrarcargando();
                console.log('Error:', data);
                $target.html('<div class="text-danger">Error al cargar</div>');
            }
        });
    });

    $('#btnBuscar').on('click', function () {
        var cod_empresa = $('#cod_empresa').val();
        var cod_centro = $('#cod_centro').val();
        var cod_zona = $('#cod_zona').val();
        var cod_jefe_venta = $('#cod_jefe_venta').val();
        var cod_cliente = $('#cod_cliente').val();
        var fechainicio = $('#fechainicio').val();
        var fechafin = $('#fechafin').val();

        // Validaciones
        if (cod_empresa == "-1") {
            alerterrorajax("Debe seleccionar una empresa");
            return false;
        }
        if (cod_centro == "-1") {
            alerterrorajax("Debe seleccionar un centro");
            return false;
        }
        if (cod_zona == "-1") {
            alerterrorajax("Debe seleccionar una zona comercial");
            return false;
        }
        if (cod_jefe_venta == "-1" || cod_jefe_venta == null) {
            alerterrorajax("Debe seleccionar un jefe de venta");
            return false;
        }
        if (fechainicio == "" || fechafin == "") {
            alerterrorajax("Debe seleccionar el rango de fechas");
            return false;
        }

        // Si pasa las validaciones
        var $btn = $(this);
        var _token = $('#token').val();
        var $target = $('.ajax_lista_conciliacion_cobranza');

        $btn.prop('disabled', true);
        abrircargando();

        $.ajax({
            type: 'POST',
            url: $('#carpeta').val() + '/ajax-listar-conciliacion-cobranza',
            data: {
                _token: _token,
                fechainicio: fechainicio,
                fechafin: fechafin,
                cod_zona: cod_zona,
                cod_jefe_venta: cod_jefe_venta,
                cod_empresa: cod_empresa,
                cod_centro: cod_centro,
                cod_cliente: cod_cliente
            },
            success: function (data) {
                cerrarcargando();
                $target.html(data);
                $btn.prop('disabled', false);
            },
            error: function (data) {
                cerrarcargando();
                console.log('Error:', data);
                $target.html('<div class="alert alert-danger">Ocurrió un error al procesar el reporte.</div>');
                $btn.prop('disabled', false);
                alert("Error técnico al solicitar los datos");
            }
        });
    });

});
