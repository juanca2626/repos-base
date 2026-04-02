<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Incorporar Vue en otra página web</title>
  <!-- Incluir Vue.js desde un CDN -->
  <script src="https://cdn.jsdelivr.net/npm/vue@2"></script>
  <!-- Incluir tu archivo con el componente Vue -->
  <script src="http://a3front.limatours.test:5173/public/passengers.js" defer></script>
</head>
<body>
  <!-- Elemento donde se montará el componente -->
  <div id="passengers">
    <passengers-component></passengers-component>
  </div>
</body>
</html>