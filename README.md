# PHP Code Minifier

:star: Star us on GitHub — it motivates us a lot! 😀

PHP Code Minifier is a tool that allows you to minify your PHP code.
It removes all the unnecessary spaces and new lines from your PHP code and then split
all the PHP code in the given `.php` file into one line.

I don't know why you would want to do this, but feel free to use it if you want to :smile:

## Installation

You can install PHP Code Minifier using [Composer](https://getcomposer.org/):

```bash
composer require php-code-minifier/php-code-minifier
```

## Usage

Keep in mind, PHP code inside short opening PHP tags (`<?`) will not be minified. Such code 
parsed by PHP as HTML and will be ignored by the minifier.

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

## Contributing

Feel free to contribute to this project by submitting a pull request to 
add more features or fix bugs (or maybe add some bug? who knows :ok_hand:).

I'm going to write some contributing notes soon.

## License

PHP Code Minifier is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
