<div class="form-group">
    <label>Jefe Venta:</label>
    <select name="cod_jefe_venta" id="cod_jefe_venta" class="form-control select2">
        @foreach($combo_jefes as $item)
            <option value="{{ $item->COD }}" {{ $item->COD == '-1' ? 'selected' : '' }}>{{ $item->NOMBRE }}</option>
        @endforeach
    </select>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $(".select2").select2({
            width: '100%'
        });

        $('#cod_jefe_venta').on('change', function () {
            var cod_jefe_venta = $(this).val();
            var _token = $('#token').val();
            var $target = $('.ajax_cliente');

            abrircargando();

            $.ajax({
                type: 'POST',
                url: $('#carpeta').val() + '/ajax-listar-clientes',
                data: {
                    cod_jefe_venta: cod_jefe_venta,
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
    });
</script>