<?php

/**
 * Class AutoloaderTest
 */
class HandlebarsTest extends PHPUnit\Framework\TestCase
{
    /**
     * Test handlebars autoloader
     *
     * @return void
     */
    public function testAutoLoad()
    {
        Handlebars\Autoloader::register(realpath(__DIR__ . '/../fixture/'));

        $this->assertTrue(class_exists('Handlebars\\Test'));
        $this->assertTrue(class_exists('Handlebars\\Example\\Test'));
    }

    /**
     * Test basic tags
     *
     * @param string $src    handlebars source
     * @param array  $data   data
     * @param string $result expected data
     *
     * @dataProvider simpleTagdataProvider
     *
     * @return void
     */
    public function testBasicTags($src, $data, $result)
    {
        $loader = new \Handlebars\Loader\StringLoader();
        $engine = new \Handlebars\Handlebars(array('loader' => $loader));
        $this->assertEquals($result, $engine->render($src, $data));
    }

    /**
     * Simple tag provider
     *
     * @return array
     */
    public function simpleTagdataProvider()
    {
        return array(
            array(
                '{{! This is comment}}',
                array(),
                ''
            ),
            array(
                '{{data}}',
                array('data' => 'result'),
                'result'
            ),
            array(
                '{{data.key}}',
                array('data' => array('key' => 'result')),
                'result'
            ),
        );
    }


    /**
     * Test helpers (internal helpers)
     *
     * @param string $src    handlebars source
     * @param array  $data   data
     * @param string $result expected data
     *
     * @dataProvider internalHelpersdataProvider
     *
     * @return void
     */
    public function testSimpleHelpers($src, $data, $result)
    {
        $loader = new \Handlebars\Loader\StringLoader();
        $helpers = new \Handlebars\Helpers();
        $engine = new \Handlebars\Handlebars(array('loader' => $loader, 'helpers' => $helpers));

        $this->assertEquals($result, $engine->render($src, $data));
    }

    /**
     * Simple helpers provider
     *
     * @return array
     */
    public function internalHelpersdataProvider()
    {
        return [
            [
                '{{#if data}}Yes{{/if}}',
                ['data' => true],
                'Yes'
            ],
            [
                '{{#if data}}Yes{{/if}}',
                ['data' => false],
                ''
            ],
            [
                '{{#unless data}}OK{{/unless}}',
                ['data' => false],
                'OK'
            ],
            [
                '{{#unless data}}OK {{else}}I believe{{/unless}}',
                ['data' => true],
                'I believe'
            ],
            [
                '{{#with data}}{{key}}{{/with}}',
                ['data' => ['key' => 'result']],
                'result'
            ],
            [
                '{{#each data}}{{this}}{{/each}}',
                ['data' => [1, 2, 3, 4]],
                '1234'
            ],
            [
                '{{#each data[0:2]}}{{this}}{{/each}}',
                ['data' => [1, 2, 3, 4]],
                '12'
            ],
            [
                '{{#each data[1:2]}}{{this}}{{/each}}',
                ['data' => [1, 2, 3, 4]],
                '23'
            ],
            [
                '{{#upper data}}',
                ['data' => "hello"],
                'HELLO'
            ],
            [
                '{{#lower data}}',
                ['data' => "HELlO"],
                'hello'
            ],
            [
                '{{#capitalize data}}',
                ['data' => "hello"],
                'Hello'
            ],
            [
                '{{#capitalize_words data}}',
                ['data' => "hello world"],
                'Hello World'
            ],
            [
                '{{#reverse data}}',
                ['data' => "hello"],
                'olleh'
            ],
            [
                "{{#inflect count 'album' 'albums' }}",
                ["count" => 1],
                'album'
            ],
            [
                "{{#inflect count 'album' 'albums' }}",
                ["count" => 10],
                'albums'
            ],
            [
                "{{#inflect count '%d album' '%d albums' }}",
                ["count" => 1],
                '1 album'
            ],
            [
                "{{#inflect count '%d album' '%d albums' }}",
                ["count" => 10],
                '10 albums'
            ],
            [
                "{{#default data 'OK' }}",
                ["data" => "hello"],
                'hello'
            ],
            [
                "{{#default data 'OK' }}",
                [],
                'OK'
            ],
            [
                "{{#truncate data 8 '...'}}",
                ["data" => "Hello World! How are you?"],
                'Hello Wo...'
            ],
            [
                "{{#raw}}I'm raw {{data}}{{/raw}}",
                ["data" => "raw to be included, but won't :)"],
                "I'm raw {{data}}"
            ],
            [
                "{{#repeat 3}}Yes {{/repeat}}",
                [],
                "Yes Yes Yes "
            ],
            [
                "{{#repeat 4}}Nice {{data}} {{/repeat}}",
                ["data" => "Daddy!"],
                "Nice Daddy! Nice Daddy! Nice Daddy! Nice Daddy! "
            ],
            [
                "{{#define test}}I'm Defined and Invoked{{/define}}{{#invoke test}}",
                [],
                "I'm Defined and Invoked"
            ],

            // --- elseif: basic truthiness ---
            [
                '{{#if a}}A{{elseif b}}B{{else}}C{{/if}}',
                ['a' => false, 'b' => true],
                'B'
            ],
            [
                '{{#if a}}A{{elseif b}}B{{else}}C{{/if}}',
                ['a' => false, 'b' => false],
                'C'
            ],
            [
                '{{#if a}}A{{elseif b}}B{{else}}C{{/if}}',
                ['a' => true, 'b' => true],
                'A'
            ],

            // --- elseif: multiple elseif branches ---
            [
                '{{#if a}}A{{elseif b}}B{{elseif c}}C{{else}}D{{/if}}',
                ['a' => false, 'b' => false, 'c' => true],
                'C'
            ],
            [
                '{{#if a}}A{{elseif b}}B{{elseif c}}C{{else}}D{{/if}}',
                ['a' => false, 'b' => false, 'c' => false],
                'D'
            ],

            // --- if: equality comparison with string literal (backtick expr) ---
            [
                '{{#if `type == "product"`}}yes{{else}}no{{/if}}',
                ['type' => 'product'],
                'yes'
            ],
            [
                '{{#if `type == "product"`}}yes{{else}}no{{/if}}',
                ['type' => 'other'],
                'no'
            ],

            // --- if: inequality comparison with string literal ---
            [
                '{{#if `type != "product"`}}yes{{else}}no{{/if}}',
                ['type' => 'service'],
                'yes'
            ],
            [
                '{{#if `type != "product"`}}yes{{else}}no{{/if}}',
                ['type' => 'product'],
                'no'
            ],

            // --- if: equality comparison between two context variables ---
            [
                '{{#if `a == b`}}equal{{else}}nope{{/if}}',
                ['a' => 'hello', 'b' => 'hello'],
                'equal'
            ],
            [
                '{{#if `a == b`}}equal{{else}}nope{{/if}}',
                ['a' => 'hello', 'b' => 'world'],
                'nope'
            ],

            // --- if: inequality comparison between two context variables ---
            [
                '{{#if `a != b`}}diff{{else}}same{{/if}}',
                ['a' => 'foo', 'b' => 'bar'],
                'diff'
            ],
            [
                '{{#if `a != b`}}diff{{else}}same{{/if}}',
                ['a' => 'foo', 'b' => 'foo'],
                'same'
            ],

            // --- elseif: comparison expression in elseif branch ---
            [
                '{{#if isActive}}active{{elseif `type == "special"`}}special{{else}}default{{/if}}',
                ['isActive' => false, 'type' => 'special'],
                'special'
            ],
            [
                '{{#if isActive}}active{{elseif `type == "special"`}}special{{else}}default{{/if}}',
                ['isActive' => false, 'type' => 'normal'],
                'default'
            ],

            // --- if: numeric literal comparison ---
            [
                '{{#if `count == 3`}}three{{else}}other{{/if}}',
                ['count' => '3'],
                'three'
            ],
            [
                '{{#if `count != 0`}}nonzero{{else}}zero{{/if}}',
                ['count' => '5'],
                'nonzero'
            ],

            // --- if: greater-than / less-than with integers ---
            [
                '{{#if `count > 5`}}big{{else}}small{{/if}}',
                ['count' => 10],
                'big'
            ],
            [
                '{{#if `count > 5`}}big{{else}}small{{/if}}',
                ['count' => 3],
                'small'
            ],
            [
                '{{#if `count < 5`}}small{{else}}big{{/if}}',
                ['count' => 2],
                'small'
            ],
            [
                '{{#if `count < 5`}}small{{else}}big{{/if}}',
                ['count' => 7],
                'big'
            ],

            // --- if: greater-than-or-equal / less-than-or-equal ---
            [
                '{{#if `count >= 5`}}yes{{else}}no{{/if}}',
                ['count' => 5],
                'yes'
            ],
            [
                '{{#if `count >= 5`}}yes{{else}}no{{/if}}',
                ['count' => 4],
                'no'
            ],
            [
                '{{#if `count <= 5`}}yes{{else}}no{{/if}}',
                ['count' => 5],
                'yes'
            ],
            [
                '{{#if `count <= 5`}}yes{{else}}no{{/if}}',
                ['count' => 6],
                'no'
            ],

            // --- if: numeric string values are coerced (not lexicographic) ---
            [
                '{{#if `count > 9`}}yes{{else}}no{{/if}}',
                ['count' => '10'],
                'yes'
            ],
            [
                '{{#if `count < 10`}}yes{{else}}no{{/if}}',
                ['count' => '9'],
                'yes'
            ],

            // --- if: float comparisons ---
            [
                '{{#if `price >= 9.99`}}yes{{else}}no{{/if}}',
                ['price' => 10.0],
                'yes'
            ],
            [
                '{{#if `price >= 9.99`}}yes{{else}}no{{/if}}',
                ['price' => 9.98],
                'no'
            ],
            [
                '{{#if `price < 1.5`}}cheap{{else}}expensive{{/if}}',
                ['price' => 1.0],
                'cheap'
            ],
            [
                '{{#if `price < 1.5`}}cheap{{else}}expensive{{/if}}',
                ['price' => 1.5],
                'expensive'
            ],
            [
                '{{#if `score == 3.14`}}pi{{else}}nope{{/if}}',
                ['score' => 3.14],
                'pi'
            ],

            // --- if: comparing two context variables numerically ---
            [
                '{{#if `a > b`}}yes{{else}}no{{/if}}',
                ['a' => 10, 'b' => 5],
                'yes'
            ],
            [
                '{{#if `a > b`}}yes{{else}}no{{/if}}',
                ['a' => 5, 'b' => 10],
                'no'
            ],
            [
                '{{#if `a <= b`}}yes{{else}}no{{/if}}',
                ['a' => 5, 'b' => 5],
                'yes'
            ],

            // --- elseif: comparison in elseif with > / < ---
            [
                '{{#if `count < 0`}}negative{{elseif `count == 0`}}zero{{elseif `count > 0`}}positive{{/if}}',
                ['count' => 7],
                'positive'
            ],
            [
                '{{#if `count < 0`}}negative{{elseif `count == 0`}}zero{{elseif `count > 0`}}positive{{/if}}',
                ['count' => 0],
                'zero'
            ],
            [
                '{{#if `count < 0`}}negative{{elseif `count == 0`}}zero{{elseif `count > 0`}}positive{{/if}}',
                ['count' => -3],
                'negative'
            ],

        ];
    }

    /**
     * @param string $src
     * @param array $data
     * @param string $result
     * @param bool $enableDataVariables
     * @dataProvider internalDataVariablesDataProvider
     */
    public function testDataVariables($src, $data, $result, $enableDataVariables)
    {
        $loader = new \Handlebars\Loader\StringLoader();
        $helpers = new \Handlebars\Helpers();
        $engine = new \Handlebars\Handlebars(array(
            'loader' => $loader,
            'helpers' => $helpers,
            'enableDataVariables'=> $enableDataVariables,
        ));

        $this->assertEquals($result, $engine->render($src, $data));
    }

    public function testDataVariables1()
    {
        $object = new stdClass;
        $object->{'@first'} = 'apple';
        $object->{'@last'} = 'banana';
        $object->{'@index'} = 'carrot';
        $object->{'@unknown'} = 'zucchini';
        $data = ['data' => [$object]];
        $engine = new \Handlebars\Handlebars(array(
            'loader' => new \Handlebars\Loader\StringLoader(),
            'helpers' => new \Handlebars\Helpers(),
            'enableDataVariables'=> false,
        ));
        $template = "{{#each data}}{{@first}}, {{@last}}, {{@index}}, {{@unknown}}{{/each}}";

        $this->assertEquals('apple, banana, 0, zucchini', $engine->render($template, $data));
    }

    /**
     * Data provider for data variables
     * @return array
     */
    public function internalDataVariablesDataProvider()
    {
        // Build a standard set of objects to test against
        $keyPropertyName = '@key';
        $firstPropertyName = '@first';
        $lastPropertyName = '@last';
        $unknownPropertyName = '@unknown';
        $objects = [];
        foreach (['apple', 'banana', 'carrot', 'zucchini'] as $itemValue) {
            $object = new stdClass();
            $object->$keyPropertyName = $itemValue;
            $object->$firstPropertyName = $itemValue;
            $object->$lastPropertyName = $itemValue;
            $object->$unknownPropertyName = $itemValue;
            $objects[] = $object;
        }

        // Build a list of scenarios. These will be used later to build fanned out scenarios that will be used against
        // the test. Each entry represents two different tests: (1) when enableDataVariables is enabled and (2) not enabled.
        $scenarios = [
            [
                'src' => '{{#each data}}{{@index}}{{/each}}',
                'data' => ['data' => ['apple', 'banana', 'carrot', 'zucchini']],
                // @index should work the same regardless of the feature flag
                'outputNotEnabled' => '0123',
                'outputEnabled' => '0123',
            ],
            [
                'src' => '{{#each data}}{{@key}}{{/each}}',
                'data' => ['data' => ['apple', 'banana', 'carrot', 'zucchini']],
                'outputNotEnabled' => '',
                'outputEnabled' => '0123'
            ],
            [
                'src' => '{{#each data}}{{#each this}}outer: {{@../key}},inner: {{@key}};{{/each}}{{/each}}',
                'data' => ['data' => [['apple', 'banana'], ['carrot', 'zucchini']]],
                'outputNotEnabled' => 'outer: ,inner: ;outer: ,inner: ;outer: ,inner: ;outer: ,inner: ;',
                'outputEnabled' => 'outer: 0,inner: 0;outer: 0,inner: 1;outer: 1,inner: 0;outer: 1,inner: 1;',
            ],
            [
                'src' => '{{#each data}}{{#if @first}}true{{else}}false{{/if}}{{/each}}',
                'data' => ['data' => ['apple', 'banana', 'carrot', 'zucchini']],
                'outputNotEnabled' => 'falsefalsefalsefalse',
                'outputEnabled' => 'truefalsefalsefalse',
            ],
            [
                'src' => '{{#each data}}{{@first}}{{/each}}',
                'data' => ['data' => ['apple', 'banana', 'carrot', 'zucchini']],
                'outputNotEnabled' => '',
                'outputEnabled' => 'truefalsefalsefalse',
            ],
            [
                'src' => '{{#each data}}{{#each this}}outer: {{@../first}},inner: {{@first}};{{/each}}{{/each}}',
                'data' => ['data' => [['apple', 'banana'], ['carrot', 'zucchini']]],
                'outputNotEnabled' => 'outer: ,inner: ;outer: ,inner: ;outer: ,inner: ;outer: ,inner: ;',
                'outputEnabled' => 'outer: true,inner: true;outer: true,inner: false;outer: false,inner: true;outer: false,inner: false;',
            ],
            [
                'src' => '{{#each data}}{{#if @last}}true{{else}}false{{/if}}{{/each}}',
                'data' => ['data' => ['apple', 'banana', 'carrot', 'zucchini']],
                'outputNotEnabled' => 'falsefalsefalsefalse',
                'outputEnabled' => 'falsefalsefalsetrue'
            ],
            [
                'src' => '{{#each data}}{{@last}}{{/each}}',
                'data' => ['data' => ['apple', 'banana', 'carrot', 'zucchini']],
                'outputNotEnabled' => '',
                'outputEnabled' => 'falsefalsefalsetrue'
            ],
            [
                'src' => '{{#each data}}{{#each this}}outer: {{@../last}},inner: {{@last}};{{/each}}{{/each}}',
                'data' => ['data' => [['apple', 'banana'], ['carrot', 'zucchini']]],
                'outputNotEnabled' => 'outer: ,inner: ;outer: ,inner: ;outer: ,inner: ;outer: ,inner: ;',
                'outputEnabled' => 'outer: false,inner: false;outer: false,inner: true;outer: true,inner: false;outer: true,inner: true;'
            ],
            [
                // @index variables are ignored and the data variable is used
                'src' => '{{#each data}}{{@index}}{{/each}}',
                'data' => ['data' => [['@index' => 'apple'], ['@index' => 'banana'], ['@index' => 'carrot'], ['@index' => 'zucchini']]],
                'outputNotEnabled' => '0123',
                'outputEnabled' => '0123'
            ],
            [
                // @key variables are ignored and the data variable is used
                'src' => '{{#each data}}{{@index}}{{/each}}',
                'data' => ['data' => $objects],
                'outputNotEnabled' => '0123',
                'outputEnabled' => '0123'
            ],
            [
                // @first variables are used when data variables are not enabled.
                'src' => '{{#each data}}{{@first}}{{/each}}',
                'data' => ['data' => $objects],
                'outputNotEnabled' => 'applebananacarrotzucchini',
                'outputEnabled' => 'truefalsefalsefalse'
            ],
            [
                // @last variables are used when data variables are not enabled.
                'src' => '{{#each data}}{{@last}}{{/each}}',
                'data' => ['data' => $objects],
                'outputNotEnabled' => 'applebananacarrotzucchini',
                'outputEnabled' => 'falsefalsefalsetrue'
            ],
            [
                // @unknown variables are used when data variables are not enabled however since "unknown" is not a valid
                // value it should ignored.
                'src' => '{{#each data}}{{@unknown}}{{/each}}',
                'data' => ['data' => $objects],
                'outputNotEnabled' => 'applebananacarrotzucchini',
                'outputEnabled' => ''
            ],
            [
                'src' => '{{#each data}}{{@key}}{{/each}}',
                'data' => ['data' => ['fruit' => 'apple', '19' => 'banana', 'true' => 'carrot']],
                'outputNotEnabled' => 'fruit19true',
                'outputEnabled' => 'fruit19true',
            ],
            [
                'src' => '{{#each items}}{{#if `@index == 1`}}[{{this}}]{{else}}{{this}}{{/if}}{{/each}}',
                'data' => ['items' => ['a', 'b', 'c']],
                'outputNotEnabled' => 'abc',
                'outputEnabled' => 'a[b]c',
            ]
        ];

        // Build out a test case for when the enableDataVariables feature is enabled and when it's not
        $fannedOutScenarios = [];
        foreach ($scenarios as $scenario) {
            $fannedOutScenarios['not enabled: ' . $scenario['src']] = [
                $scenario['src'],
                $scenario['data'],
                $scenario['outputNotEnabled'],
                false,
            ];
            $fannedOutScenarios['enabled: ' . $scenario['src']] = [
                $scenario['src'],
                $scenario['data'],
                $scenario['outputEnabled'],
                true,
            ];
        }
        return $fannedOutScenarios;
    }

    /**
     * Management helpers
     */
    public function testHelpersManagement()
    {
        $helpers = new \Handlebars\Helpers(array('test' => function () {
        }), false);
        $engine = new \Handlebars\Handlebars(array('helpers' => $helpers));
        $this->assertTrue(is_callable($engine->getHelper('test')));
        $this->assertTrue($engine->hasHelper('test'));
        $engine->removeHelper('test');
        $this->assertFalse($engine->hasHelper('test'));
    }

    /**
     * Custom helper test
     */
    public function testCustomHelper()
    {
        $loader = new \Handlebars\Loader\StringLoader();
        $engine = new \Handlebars\Handlebars(array('loader' => $loader));
        $engine->addHelper('test', function () {
            return 'Test helper is called';
        });
        $this->assertEquals('Test helper is called', $engine->render('{{#test}}', []));
    }

    /**
     * Test automatic parent-scope variable lookup introduced in Context.php.
     *
     * Variables that are not found in the current scope must be looked up in
     * parent scopes automatically (no explicit ../ required).
     *
     * @param string $src
     * @param array  $data
     * @param string $expected
     *
     * @dataProvider parentScopeProvider
     */
    public function testParentScopeLookup($src, $data, $expected)
    {
        $loader  = new \Handlebars\Loader\StringLoader();
        $helpers = new \Handlebars\Helpers();
        $engine  = new \Handlebars\Handlebars(['loader' => $loader, 'helpers' => $helpers]);
        $this->assertEquals($expected, $engine->render($src, $data));
    }

    /**
     * Data provider for parent-scope lookup tests.
     *
     * @return array
     */
    public function parentScopeProvider()
    {
        return [
            // Variable from parent scope is accessible inside {{#each}} without ../
            'implicit parent lookup in each' => [
                '{{#each items}}{{parentVar}}{{/each}}',
                ['items' => ['a', 'b'], 'parentVar' => 'X'],
                'XX',
            ],

            // Explicit ../ still works as before
            'explicit parent lookup with ../' => [
                '{{#each items}}{{../parentVar}}{{/each}}',
                ['items' => ['a', 'b'], 'parentVar' => 'Y'],
                'YY',
            ],

            // The current-scope value shadows the parent-scope value
            'current scope shadows parent scope' => [
                '{{#each items}}{{label}}{{/each}}',
                ['items' => [['label' => 'inner']], 'label' => 'outer'],
                'inner',
            ],

            // Nested {{#each}}: a variable two levels up is found automatically
            'implicit lookup two levels up' => [
                '{{#each outer}}{{#each inner}}{{rootVar}}{{/each}}{{/each}}',
                ['outer' => [['inner' => ['x', 'y']]], 'rootVar' => 'R'],
                'RR',
            ],

            // Explicit ../ from nested each reaches one level up only
            'explicit ../ inside nested each reaches nearest parent' => [
                '{{#each outer}}{{#each inner}}{{../../rootVar}}{{/each}}{{/each}}',
                ['outer' => [['inner' => ['x']]], 'rootVar' => 'ROOT'],
                'ROOT',
            ],

            // Variable present only in innermost scope, not inherited by parent
            'variable only in current scope' => [
                '{{#each items}}{{this}}{{/each}}',
                ['items' => ['one', 'two']],
                'onetwo',
            ],

            // dot-path variable from parent scope looked up automatically
            'implicit parent lookup for dot-path variable' => [
                '{{#each items}}{{meta.title}}{{/each}}',
                ['items' => ['a', 'b'], 'meta' => ['title' => 'Hello']],
                'HelloHello',
            ],
        ];
    }

    /**
     * @param $dir
     *
     * @return bool
     */
    private function delTree($dir)
    {
        $contents = scandir($dir);
        if ($contents === false) {
            return;
        }
        $files = array_diff($contents, array('.', '..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
        }

        return rmdir($dir);
    }

    /**
     * Its not a good test :) but ok
     */
    public function testCacheSystem()
    {
        $path = sys_get_temp_dir() . '/__cache__handlebars';

        @$this->delTree($path);

        $dummy = new \Handlebars\Cache\Disk($path);
        $engine = new \Handlebars\Handlebars(array('cache' => $dummy));
        $this->assertEquals(0, count(glob($path . '/*')));
        $engine->render('test', array());
        $this->assertEquals(1, count(glob($path . '/*')));
    }
}
