<?php

echo <<<EOF
hello!
how's it going?
EOF;

function test(): string
{
    return <<<'PHP'
    <?php
    
    echo 'This is a test';

    PHP;
}

$id = 'magic-btn';

$x = [
    <<<'JS'
    
    const something = () => {
        // ...    
    }
    
    JS,
    <<<HTML
        <button id="$id">
            Click me, i dare you.
        </button>
    HTML,
];

if (true) {
    if (true) {
        if (true) {
            if (true) {
                if (true) {
                    if (true) {
                        printf(<<<'EOF'
This is a pre-7.3 nowdoc.
['%s' => %d]
EOF, 'Test', 123);
                        printf(<<<'EOF'
        This has padding!
        It needs to keep it.
EOF);
                    }
                }
            }
        }
    }
}

// no indentation
echo <<<END
      a
     b
    c
\n
END;

// 4 spaces of indentation
echo <<<END
      a
     b
    c
    END;
