<form method="POST" action="#" enctype="multipart/form-data">
    {{ csrf_field() }}
    <input type="file" name="file" />
    <button type="submit">Procesar</button>
</form>
