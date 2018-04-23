<?php
/**
 * @package ex-db-mssql
 * @author Erik Zigo <erik.zigo@keboola.com>
 */
namespace Keboola\DbExtractor;

use Keboola\Csv\CsvFile;
use Keboola\DbExtractor\Test\ExtractorTest;
use Symfony\Component\Yaml\Yaml;
use Nette\Utils;

class MSSQLTest extends AbstractMSSQLTest
{
    public function testCredentials()
    {
        $config = $this->getConfig('mssql');
        $config['action'] = 'testConnection';
        unset($config['parameters']['tables']);

        $app = $this->createApplication($config);
        $result = $app->run();

        $this->assertArrayHasKey('status', $result);
        $this->assertEquals('success', $result['status']);
    }

    public function testRunWithoutTables()
    {
        $config = $this->getConfig('mssql');

        unset($config['parameters']['tables']);

        $app = $this->createApplication($config);
        $result = $app->run();

        $this->assertArrayHasKey('status', $result);
        $this->assertEquals('success', $result['status']);
    }

    /**
     * @dataProvider configProvider
     * @param $config
     */
    public function testRunConfig($config)
    {
        $result = $this->createApplication($config)->run();

        $this->checkResult($result);
    }

    public function testRunWithSSH()
    {
        $config = $this->getConfig('mssql', 'json');
        $config['parameters']['db']['ssh'] = [
            'enabled' => true,
            'keys' => [
                '#private' => $this->getPrivateKey('mssql'),
                'public' => $this->getEnv('mssql', 'DB_SSH_KEY_PUBLIC')
            ],
            'user' => 'root',
            'sshHost' => 'sshproxy',
            'remoteHost' => 'mssql',
            'remotePort' => '1433',
            'localPort' => '1234',
        ];

        $result = $this->createApplication($config)->run();

        $this->checkResult($result);
    }

    private function checkResult($result)
    {
        $this->assertEquals('success', $result['status']);
        $this->assertEquals(
            array (
                0 =>
                    array (
                        'outputTable' => 'in.c-main.sales',
                        'rows' => 100,
                    ),
                1 =>
                    array (
                        'outputTable' => 'in.c-main.tableColumns',
                        'rows' => 100,
                    ),
                2 =>
                    array (
                        'outputTable' => 'in.c-main.auto-increment-timestamp',
                        'rows' => 6,
                    ),
            ),
            $result['imported']
        );

        $salesManifestFile = $this->dataDir . '/out/tables/' . $result['imported'][0]['outputTable'] . '.csv.manifest';
        $manifest = json_decode(file_get_contents($salesManifestFile), true);
        $this->assertEquals(['destination' => 'in.c-main.sales', 'incremental' => false], $manifest);

        $tableColumnsManifest = $this->dataDir . '/out/tables/' . $result['imported'][1]['outputTable'] . '.csv.manifest';
        $manifest = json_decode(file_get_contents($tableColumnsManifest), true);
        $this->assertEquals(
            array (
                'destination' => 'in.c-main.tableColumns',
                'incremental' => false,
                'metadata' =>
                    array (
                        0 =>
                            array (
                                'key' => 'KBC.name',
                                'value' => 'sales',
                            ),
                        1 =>
                            array (
                                'key' => 'KBC.catalog',
                                'value' => 'test',
                            ),
                        2 =>
                            array (
                                'key' => 'KBC.schema',
                                'value' => 'dbo',
                            ),
                        3 =>
                            array (
                                'key' => 'KBC.type',
                                'value' => 'BASE TABLE',
                            ),
                    ),
                'column_metadata' =>
                    array (
                        'usergender' =>
                            array (
                                0 =>
                                    array (
                                        'key' => 'KBC.datatype.type',
                                        'value' => 'varchar',
                                    ),
                                1 =>
                                    array (
                                        'key' => 'KBC.datatype.nullable',
                                        'value' => true,
                                    ),
                                2 =>
                                    array (
                                        'key' => 'KBC.datatype.basetype',
                                        'value' => 'STRING',
                                    ),
                                3 =>
                                    array (
                                        'key' => 'KBC.datatype.length',
                                        'value' => 255,
                                    ),
                                4 =>
                                    array (
                                        'key' => 'KBC.sourceName',
                                        'value' => 'usergender',
                                    ),
                                5 =>
                                    array (
                                        'key' => 'KBC.sanitizedName',
                                        'value' => 'usergender',
                                    ),
                                6 =>
                                    array (
                                        'key' => 'KBC.ordinalPosition',
                                        'value' => 1,
                                    ),
                                7 =>
                                    array (
                                        'key' => 'KBC.primaryKey',
                                        'value' => false,
                                    ),
                            ),
                        'usercity' =>
                            array (
                                0 =>
                                    array (
                                        'key' => 'KBC.datatype.type',
                                        'value' => 'varchar',
                                    ),
                                1 =>
                                    array (
                                        'key' => 'KBC.datatype.nullable',
                                        'value' => true,
                                    ),
                                2 =>
                                    array (
                                        'key' => 'KBC.datatype.basetype',
                                        'value' => 'STRING',
                                    ),
                                3 =>
                                    array (
                                        'key' => 'KBC.datatype.length',
                                        'value' => 255,
                                    ),
                                4 =>
                                    array (
                                        'key' => 'KBC.sourceName',
                                        'value' => 'usercity',
                                    ),
                                5 =>
                                    array (
                                        'key' => 'KBC.sanitizedName',
                                        'value' => 'usercity',
                                    ),
                                6 =>
                                    array (
                                        'key' => 'KBC.ordinalPosition',
                                        'value' => 2,
                                    ),
                                7 =>
                                    array (
                                        'key' => 'KBC.primaryKey',
                                        'value' => false,
                                    ),
                            ),
                        'usersentiment' =>
                            array (
                                0 =>
                                    array (
                                        'key' => 'KBC.datatype.type',
                                        'value' => 'varchar',
                                    ),
                                1 =>
                                    array (
                                        'key' => 'KBC.datatype.nullable',
                                        'value' => true,
                                    ),
                                2 =>
                                    array (
                                        'key' => 'KBC.datatype.basetype',
                                        'value' => 'STRING',
                                    ),
                                3 =>
                                    array (
                                        'key' => 'KBC.datatype.length',
                                        'value' => 255,
                                    ),
                                4 =>
                                    array (
                                        'key' => 'KBC.sourceName',
                                        'value' => 'usersentiment',
                                    ),
                                5 =>
                                    array (
                                        'key' => 'KBC.sanitizedName',
                                        'value' => 'usersentiment',
                                    ),
                                6 =>
                                    array (
                                        'key' => 'KBC.ordinalPosition',
                                        'value' => 3,
                                    ),
                                7 =>
                                    array (
                                        'key' => 'KBC.primaryKey',
                                        'value' => false,
                                    ),
                            ),
                        'zipcode' =>
                            array (
                                0 =>
                                    array (
                                        'key' => 'KBC.datatype.type',
                                        'value' => 'varchar',
                                    ),
                                1 =>
                                    array (
                                        'key' => 'KBC.datatype.nullable',
                                        'value' => true,
                                    ),
                                2 =>
                                    array (
                                        'key' => 'KBC.datatype.basetype',
                                        'value' => 'STRING',
                                    ),
                                3 =>
                                    array (
                                        'key' => 'KBC.datatype.length',
                                        'value' => 255,
                                    ),
                                4 =>
                                    array (
                                        'key' => 'KBC.sourceName',
                                        'value' => 'zipcode',
                                    ),
                                5 =>
                                    array (
                                        'key' => 'KBC.sanitizedName',
                                        'value' => 'zipcode',
                                    ),
                                6 =>
                                    array (
                                        'key' => 'KBC.ordinalPosition',
                                        'value' => 4,
                                    ),
                                7 =>
                                    array (
                                        'key' => 'KBC.primaryKey',
                                        'value' => false,
                                    ),
                            ),
                    ),
                'columns' =>
                    array (
                        0 => 'usergender',
                        1 => 'usercity',
                        2 => 'usersentiment',
                        3 => 'zipcode',
                    ),
            ),
            $manifest
        );

        $weirdManifest = $this->dataDir . '/out/tables/' . $result['imported'][2]['outputTable'] . '.csv.manifest';
        $manifest = json_decode(file_get_contents($weirdManifest), true);
        $this->assertEquals(
            array (
                'destination' => 'in.c-main.auto-increment-timestamp',
                'incremental' => false,
                'primary_key' =>
                    array (
                        0 => 'Weir_d_I_D',
                    ),
                'metadata' =>
                    array (
                        0 =>
                            array (
                                'key' => 'KBC.name',
                                'value' => 'auto Increment Timestamp',
                            ),
                        1 =>
                            array (
                                'key' => 'KBC.catalog',
                                'value' => 'test',
                            ),
                        2 =>
                            array (
                                'key' => 'KBC.schema',
                                'value' => 'dbo',
                            ),
                        3 =>
                            array (
                                'key' => 'KBC.type',
                                'value' => 'BASE TABLE',
                            ),
                    ),
                'column_metadata' =>
                    array (
                        'Weir_d_I_D' =>
                            array (
                                0 =>
                                    array (
                                        'key' => 'KBC.datatype.type',
                                        'value' => 'int',
                                    ),
                                1 =>
                                    array (
                                        'key' => 'KBC.datatype.nullable',
                                        'value' => false,
                                    ),
                                2 =>
                                    array (
                                        'key' => 'KBC.datatype.basetype',
                                        'value' => 'INTEGER',
                                    ),
                                3 =>
                                    array (
                                        'key' => 'KBC.datatype.length',
                                        'value' => 10,
                                    ),
                                4 =>
                                    array (
                                        'key' => 'KBC.sourceName',
                                        'value' => '_Weir%d I-D',
                                    ),
                                5 =>
                                    array (
                                        'key' => 'KBC.sanitizedName',
                                        'value' => 'Weir_d_I_D',
                                    ),
                                6 =>
                                    array (
                                        'key' => 'KBC.ordinalPosition',
                                        'value' => 1,
                                    ),
                                7 =>
                                    array (
                                        'key' => 'KBC.primaryKey',
                                        'value' => true,
                                    ),
                                8 =>
                                    array (
                                        'key' => 'KBC.primaryKeyName',
                                        'value' => 'PK_AUTOINC',
                                    ),
                                9 =>
                                    array (
                                        'key' => 'KBC.checkConstraint',
                                        'value' => 'CHK_ID_CONTSTRAINT',
                                    ),
                                10 =>
                                    array (
                                        'key' => 'KBC.checkClause',
                                        'value' => '([_Weir%d I-D] > 0 and [_Weir%d I-D] < 20)',
                                    ),
                            ),
                        'Weir_d_Na_me' =>
                            array (
                                0 =>
                                    array (
                                        'key' => 'KBC.datatype.type',
                                        'value' => 'varchar',
                                    ),
                                1 =>
                                    array (
                                        'key' => 'KBC.datatype.nullable',
                                        'value' => false,
                                    ),
                                2 =>
                                    array (
                                        'key' => 'KBC.datatype.basetype',
                                        'value' => 'STRING',
                                    ),
                                3 =>
                                    array (
                                        'key' => 'KBC.datatype.length',
                                        'value' => 55,
                                    ),
                                4 =>
                                    array (
                                        'key' => 'KBC.datatype.default',
                                        'value' => '(\'mario\')',
                                    ),
                                5 =>
                                    array (
                                        'key' => 'KBC.sourceName',
                                        'value' => 'Weir%d Na-me',
                                    ),
                                6 =>
                                    array (
                                        'key' => 'KBC.sanitizedName',
                                        'value' => 'Weir_d_Na_me',
                                    ),
                                7 =>
                                    array (
                                        'key' => 'KBC.ordinalPosition',
                                        'value' => 2,
                                    ),
                                8 =>
                                    array (
                                        'key' => 'KBC.primaryKey',
                                        'value' => false,
                                    ),
                                9 =>
                                    array (
                                        'key' => 'KBC.uniqueKey',
                                        'value' => true,
                                    ),
                                10 =>
                                    array (
                                        'key' => 'KBC.uniqueKeyName',
                                        'value' => 'UNI_KEY_1',
                                    ),
                            ),
                        'type' =>
                            array (
                                0 =>
                                    array (
                                        'key' => 'KBC.datatype.type',
                                        'value' => 'varchar',
                                    ),
                                1 =>
                                    array (
                                        'key' => 'KBC.datatype.nullable',
                                        'value' => true,
                                    ),
                                2 =>
                                    array (
                                        'key' => 'KBC.datatype.basetype',
                                        'value' => 'STRING',
                                    ),
                                3 =>
                                    array (
                                        'key' => 'KBC.datatype.length',
                                        'value' => 55,
                                    ),
                                4 =>
                                    array (
                                        'key' => 'KBC.sourceName',
                                        'value' => 'type',
                                    ),
                                5 =>
                                    array (
                                        'key' => 'KBC.sanitizedName',
                                        'value' => 'type',
                                    ),
                                6 =>
                                    array (
                                        'key' => 'KBC.ordinalPosition',
                                        'value' => 3,
                                    ),
                                7 =>
                                    array (
                                        'key' => 'KBC.primaryKey',
                                        'value' => false,
                                    ),
                                8 =>
                                    array (
                                        'key' => 'KBC.uniqueKey',
                                        'value' => true,
                                    ),
                                9 =>
                                    array (
                                        'key' => 'KBC.uniqueKeyName',
                                        'value' => 'UNI_KEY_1',
                                    ),
                            ),
                        'timestamp' =>
                            array (
                                0 =>
                                    array (
                                        'key' => 'KBC.datatype.type',
                                        'value' => 'datetime',
                                    ),
                                1 =>
                                    array (
                                        'key' => 'KBC.datatype.nullable',
                                        'value' => true,
                                    ),
                                2 =>
                                    array (
                                        'key' => 'KBC.datatype.basetype',
                                        'value' => 'TIMESTAMP',
                                    ),
                                3 =>
                                    array (
                                        'key' => 'KBC.datatype.length',
                                        'value' => '23,3',
                                    ),
                                4 =>
                                    array (
                                        'key' => 'KBC.datatype.default',
                                        'value' => '(getdate())',
                                    ),
                                5 =>
                                    array (
                                        'key' => 'KBC.sourceName',
                                        'value' => 'timestamp',
                                    ),
                                6 =>
                                    array (
                                        'key' => 'KBC.sanitizedName',
                                        'value' => 'timestamp',
                                    ),
                                7 =>
                                    array (
                                        'key' => 'KBC.ordinalPosition',
                                        'value' => 4,
                                    ),
                                8 =>
                                    array (
                                        'key' => 'KBC.primaryKey',
                                        'value' => false,
                                    ),
                            ),
                    ),
                'columns' =>
                    array (
                        0 => 'Weir_d_I_D',
                        1 => 'Weir_d_Na_me',
                        2 => 'type',
                        3 => 'timestamp',
                    ),
            ),
            $manifest
        );
    }

    public function testCredentialsWithSSH()
    {
        $config = $this->getConfig('mssql');
        $config['action'] = 'testConnection';

        $config['parameters']['db']['ssh'] = [
         'enabled' => true,
         'keys' => [
          '#private' => $this->getPrivateKey('mssql'),
          'public' => $this->getEnv('mssql', 'DB_SSH_KEY_PUBLIC')
         ],
         'user' => 'root',
         'sshHost' => 'sshproxy',
         'remoteHost' => 'mssql',
         'remotePort' => '1433',
         'localPort' => '1235',
        ];

        unset($config['parameters']['tables']);

        $app = $this->createApplication($config);
        $result = $app->run();

        $this->assertArrayHasKey('status', $result);
        $this->assertEquals('success', $result['status']);
    }

    public function testGetTables()
    {
        $config = $this->getConfig();
        $config['action'] = 'getTables';

        $app = new Application($config);
        $result = $app->run();

        $this->assertArrayHasKey('status', $result);
        $this->assertArrayHasKey('tables', $result);
        $this->assertEquals('success', $result['status']);
        $this->assertCount(3, $result['tables']);
        $expectedData = array (
            0 =>
                array (
                    'name' => 'auto Increment Timestamp',
                    'catalog' => 'test',
                    'schema' => 'dbo',
                    'type' => 'BASE TABLE',
                    'columns' =>
                        array (
                            2 =>
                                array (
                                    'name' => 'type',
                                    'sanitizedName' => 'type',
                                    'type' => 'varchar',
                                    'length' => 55,
                                    'nullable' => true,
                                    'default' => NULL,
                                    'ordinalPosition' => 3,
                                    'primaryKey' => false,
                                ),
                            3 =>
                                array (
                                    'name' => 'timestamp',
                                    'sanitizedName' => 'timestamp',
                                    'type' => 'datetime',
                                    'length' => '23,3',
                                    'nullable' => true,
                                    'default' => '(getdate())',
                                    'ordinalPosition' => 4,
                                    'primaryKey' => false,
                                ),
                            0 =>
                                array (
                                    'name' => '_Weir%d I-D',
                                    'sanitizedName' => 'Weir_d_I_D',
                                    'type' => 'int',
                                    'length' => 10,
                                    'nullable' => false,
                                    'default' => NULL,
                                    'ordinalPosition' => 1,
                                    'primaryKey' => true,
                                    'primaryKeyName' => 'PK_AUTOINC',
                                ),
                            1 =>
                                array (
                                    'name' => 'Weir%d Na-me',
                                    'sanitizedName' => 'Weir_d_Na_me',
                                    'type' => 'varchar',
                                    'length' => 55,
                                    'nullable' => false,
                                    'default' => '(\'mario\')',
                                    'ordinalPosition' => 2,
                                    'primaryKey' => false,
                                ),
                        ),
                ),
            1 =>
                array (
                    'name' => 'sales',
                    'catalog' => 'test',
                    'schema' => 'dbo',
                    'type' => 'BASE TABLE',
                    'columns' =>
                        array (
                            0 =>
                                array (
                                    'name' => 'usergender',
                                    'sanitizedName' => 'usergender',
                                    'type' => 'varchar',
                                    'length' => 255,
                                    'nullable' => true,
                                    'default' => NULL,
                                    'ordinalPosition' => 1,
                                    'primaryKey' => false,
                                ),
                            1 =>
                                array (
                                    'name' => 'usercity',
                                    'sanitizedName' => 'usercity',
                                    'type' => 'varchar',
                                    'length' => 255,
                                    'nullable' => true,
                                    'default' => NULL,
                                    'ordinalPosition' => 2,
                                    'primaryKey' => false,
                                ),
                            2 =>
                                array (
                                    'name' => 'usersentiment',
                                    'sanitizedName' => 'usersentiment',
                                    'type' => 'varchar',
                                    'length' => 255,
                                    'nullable' => true,
                                    'default' => NULL,
                                    'ordinalPosition' => 3,
                                    'primaryKey' => false,
                                ),
                            3 =>
                                array (
                                    'name' => 'zipcode',
                                    'sanitizedName' => 'zipcode',
                                    'type' => 'varchar',
                                    'length' => 255,
                                    'nullable' => true,
                                    'default' => NULL,
                                    'ordinalPosition' => 4,
                                    'primaryKey' => false,
                                ),
                            4 =>
                                array (
                                    'name' => 'sku',
                                    'sanitizedName' => 'sku',
                                    'type' => 'varchar',
                                    'length' => 255,
                                    'nullable' => true,
                                    'default' => NULL,
                                    'ordinalPosition' => 5,
                                    'primaryKey' => false,
                                ),
                            5 =>
                                array (
                                    'name' => 'createdat',
                                    'sanitizedName' => 'createdat',
                                    'type' => 'varchar',
                                    'length' => 64,
                                    'nullable' => false,
                                    'default' => NULL,
                                    'ordinalPosition' => 6,
                                    'primaryKey' => true,
                                    'primaryKeyName' => 'PK_sales',
                                ),
                            6 =>
                                array (
                                    'name' => 'category',
                                    'sanitizedName' => 'category',
                                    'type' => 'varchar',
                                    'length' => 255,
                                    'nullable' => true,
                                    'default' => NULL,
                                    'ordinalPosition' => 7,
                                    'primaryKey' => false,
                                ),
                            7 =>
                                array (
                                    'name' => 'price',
                                    'sanitizedName' => 'price',
                                    'type' => 'varchar',
                                    'length' => 255,
                                    'nullable' => true,
                                    'default' => NULL,
                                    'ordinalPosition' => 8,
                                    'primaryKey' => false,
                                ),
                            8 =>
                                array (
                                    'name' => 'county',
                                    'sanitizedName' => 'county',
                                    'type' => 'varchar',
                                    'length' => 255,
                                    'nullable' => true,
                                    'default' => NULL,
                                    'ordinalPosition' => 9,
                                    'primaryKey' => false,
                                ),
                            9 =>
                                array (
                                    'name' => 'countycode',
                                    'sanitizedName' => 'countycode',
                                    'type' => 'varchar',
                                    'length' => 255,
                                    'nullable' => true,
                                    'default' => NULL,
                                    'ordinalPosition' => 10,
                                    'primaryKey' => false,
                                ),
                            10 =>
                                array (
                                    'name' => 'userstate',
                                    'sanitizedName' => 'userstate',
                                    'type' => 'varchar',
                                    'length' => 255,
                                    'nullable' => true,
                                    'default' => NULL,
                                    'ordinalPosition' => 11,
                                    'primaryKey' => false,
                                ),
                            11 =>
                                array (
                                    'name' => 'categorygroup',
                                    'sanitizedName' => 'categorygroup',
                                    'type' => 'varchar',
                                    'length' => 255,
                                    'nullable' => true,
                                    'default' => NULL,
                                    'ordinalPosition' => 12,
                                    'primaryKey' => false,
                                ),
                        ),
                ),
            2 =>
                array (
                    'name' => 'sales2',
                    'catalog' => 'test',
                    'schema' => 'dbo',
                    'type' => 'BASE TABLE',
                    'columns' =>
                        array (
                            0 =>
                                array (
                                    'name' => 'usergender',
                                    'sanitizedName' => 'usergender',
                                    'type' => 'varchar',
                                    'length' => 255,
                                    'nullable' => true,
                                    'default' => NULL,
                                    'ordinalPosition' => 1,
                                    'primaryKey' => false,
                                ),
                            1 =>
                                array (
                                    'name' => 'usercity',
                                    'sanitizedName' => 'usercity',
                                    'type' => 'varchar',
                                    'length' => 255,
                                    'nullable' => true,
                                    'default' => NULL,
                                    'ordinalPosition' => 2,
                                    'primaryKey' => false,
                                ),
                            2 =>
                                array (
                                    'name' => 'usersentiment',
                                    'sanitizedName' => 'usersentiment',
                                    'type' => 'varchar',
                                    'length' => 255,
                                    'nullable' => true,
                                    'default' => NULL,
                                    'ordinalPosition' => 3,
                                    'primaryKey' => false,
                                ),
                            3 =>
                                array (
                                    'name' => 'zipcode',
                                    'sanitizedName' => 'zipcode',
                                    'type' => 'varchar',
                                    'length' => 255,
                                    'nullable' => true,
                                    'default' => NULL,
                                    'ordinalPosition' => 4,
                                    'primaryKey' => false,
                                ),
                            4 =>
                                array (
                                    'name' => 'sku',
                                    'sanitizedName' => 'sku',
                                    'type' => 'varchar',
                                    'length' => 255,
                                    'nullable' => true,
                                    'default' => NULL,
                                    'ordinalPosition' => 5,
                                    'primaryKey' => false,
                                ),
                            5 =>
                                array (
                                    'name' => 'createdat',
                                    'sanitizedName' => 'createdat',
                                    'type' => 'varchar',
                                    'length' => 64,
                                    'nullable' => false,
                                    'default' => NULL,
                                    'ordinalPosition' => 6,
                                    'primaryKey' => false,
                                ),
                            6 =>
                                array (
                                    'name' => 'category',
                                    'sanitizedName' => 'category',
                                    'type' => 'varchar',
                                    'length' => 255,
                                    'nullable' => true,
                                    'default' => NULL,
                                    'ordinalPosition' => 7,
                                    'primaryKey' => false,
                                ),
                            7 =>
                                array (
                                    'name' => 'price',
                                    'sanitizedName' => 'price',
                                    'type' => 'varchar',
                                    'length' => 255,
                                    'nullable' => true,
                                    'default' => NULL,
                                    'ordinalPosition' => 8,
                                    'primaryKey' => false,
                                ),
                            8 =>
                                array (
                                    'name' => 'county',
                                    'sanitizedName' => 'county',
                                    'type' => 'varchar',
                                    'length' => 255,
                                    'nullable' => true,
                                    'default' => NULL,
                                    'ordinalPosition' => 9,
                                    'primaryKey' => false,
                                ),
                            9 =>
                                array (
                                    'name' => 'countycode',
                                    'sanitizedName' => 'countycode',
                                    'type' => 'varchar',
                                    'length' => 255,
                                    'nullable' => true,
                                    'default' => NULL,
                                    'ordinalPosition' => 10,
                                    'primaryKey' => false,
                                ),
                            10 =>
                                array (
                                    'name' => 'userstate',
                                    'sanitizedName' => 'userstate',
                                    'type' => 'varchar',
                                    'length' => 255,
                                    'nullable' => true,
                                    'default' => NULL,
                                    'ordinalPosition' => 11,
                                    'primaryKey' => false,
                                ),
                            11 =>
                                array (
                                    'name' => 'categorygroup',
                                    'sanitizedName' => 'categorygroup',
                                    'type' => 'varchar',
                                    'length' => 255,
                                    'nullable' => true,
                                    'default' => NULL,
                                    'ordinalPosition' => 12,
                                    'primaryKey' => false,
                                ),
                        ),
                ),
        );

        $this->assertEquals($expectedData, $result['tables']);
    }
}
