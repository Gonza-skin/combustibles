{{-- data-aviso: si SweetAlert2 está cargado, el mensaje sale como aviso emergente; si no, se queda como barra. --}}
@if (session('exito'))
    <div class="alerta alerta--exito" role="status" data-aviso="success">{{ session('exito') }}</div>
@endif
@if (session('error'))
    <div class="alerta alerta--error" role="alert" data-aviso="error">{{ session('error') }}</div>
@endif
@if ($errors->any())
    <div class="alerta alerta--error" role="alert">Revisa los campos marcados: hay datos que corregir.</div>
@endif
