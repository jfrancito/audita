<div class="form-group">
    <label>Cliente:</label>
    <select name="cod_cliente" id="cod_cliente" class="form-control select2">
        @foreach($combo_clientes as $item)
            <option value="{{ $item->COD_EMPR_CLI }}" {{ $item->COD_EMPR_CLI == '-1' ? 'selected' : '' }}>
                {{ $item->NOM_EMPR_CLI }}</option>
        @endforeach
    </select>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $(".select2").select2({
            width: '100%'
        });
    });
</script>