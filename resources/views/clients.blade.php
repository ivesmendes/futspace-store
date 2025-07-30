<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Clientes – Futspace Store</title>

  {{-- CSS do Bootstrap --}}
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
    crossorigin="anonymous"
  >

  {{-- Seu JS (Vue + Bootstrap CSS importado em app.js) --}}
  @vite(['resources/js/app.js'])
</head>
{{-- A classe inicial do body será gerenciada pelo ThemeToggle --}}
<body class="bg-light text-dark"> 
  <main id="app"></main>

  {{-- JS do Bootstrap (bundle inclui Popper.js) --}}
  <script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
    crossorigin="anonymous"
  ></script>
</body>
</html>