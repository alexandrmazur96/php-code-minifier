

# Minificador de Código PHP

:star: Danos una estrella en GitHub — ¡nos motiva mucho! 😀

PHP Code Minifier es una herramienta que te permite minimizar tu código PHP.
Elimina todos los espacios y saltos de línea innecesarios de tu código PHP y luego coloca
todo el código PHP del archivo `.php` dado en una sola línea.

No sé por qué querrías hacer esto, pero siéntete libre de usarlo si lo deseas :smile:

## Instalación

Puedes instalar PHP Code Minifier usando [Composer](https://getcomposer.org/):

```bash
composer require php-code-minifier/php-code-minifier
```

## Uso

Ten en cuenta que el código PHP dentro de las etiquetas de apertura cortas (`<?`) no será minimizado. Este código 
será analizado por PHP como HTML y será ignorado por el minimizador.

```php
<?php

// Create a new instance of minifier via the factory
$phpCodeMinifier = \PhpCodeMinifier\MinifierFactory::create();

// Or, feel free to instantiate the minifier directly via new,
// but keep in mind, it's requires the PhpFileValidator and PhpTokenizer instances 
$phpCodeMinifier = new \PhpCodeMinifier\PhpMinifier(
    new \PhpCodeMinifier\Validator\PhpFileValidator(),
    new \PhpCodeMinifier\PhpTokenizer()
);

// Okay, the hardest part is done, now you can minify your PHP code
$phpCodeMinifier->minifyFile('/path/to/your/file.php');

// Or, if you already have the PHP code in a string, you can minify it with the following:
$phpCode = '<?php echo "Hello World!";';
$phpCodeMinifier->minifyString($phpCode);

// Both this action can store the minified code in a file. Just use the following:

$phpCodeMinifier->minifyStringToFile($phpCode, '/path/to/your/file.php');
// Or
$phpCodeMinifier->minifyFileToFile('/path/to/your/file.php', '/path/to/your/file.php');
```

## Contribuciones

Siéntete libre de contribuir a este proyecto enviando un pull request para 
agregar más funcionalidades o corregir errores (o quizás agregar algún error? quién sabe :ok_hand:).

Pronto escribiré algunas notas sobre cómo contribuir.

## Licencia

PHP Code Minifier está licenciado bajo la Licencia MIT - consulta el archivo [LICENSE](LICENSE) para más detalles.
