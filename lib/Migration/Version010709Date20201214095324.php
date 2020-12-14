<?php

declare(strict_types=1);

namespace OCA\Ocdownloader\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * Auto-generated migration step: Please modify to your needs!
 */
class Version010708Date20201121095324 extends SimpleMigrationStep {

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 */
	public function preSchemaChange(IOutput $output, Closure $schemaClosure, array $options) {
	}

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable('ocdownloader_queue')) {
			$table = $schema->createTable('ocdownloader_queue');
			$table->addColumn('ID', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
				'length' => 4,
			]);
			$table->addColumn('UID', 'string', [
				'notnull' => false,
				'length' => 64,
			]);
			$table->addColumn('GID', 'string', [
				'notnull' => true,
				'length' => 32,
			]);
			$table->addColumn('FILENAME', 'string', [
				'notnull' => true,
				'length' => 255,
			]);
			$table->addColumn('PROTOCOL', 'string', [
				'notnull' => true,
				'length' => 10,
			]);
			$table->addColumn('STATUS', 'smallint', [
				'notnull' => true,
				'length' => 1,
				'default' => 1,
			]);
			$table->addColumn('IS_CLEANED', 'smallint', [
				'notnull' => true,
				'length' => 1,
				'default' => 0,
			]);
			$table->addColumn('TIMESTAMP', 'bigint', [
				'notnull' => true,
				'length' => 15,
				'default' => 0,
			]);
			$table->setPrimaryKey(['ID']);
		}

		if (!$schema->hasTable('ocdownloader_adminconf')) {
			$table = $schema->createTable('ocdownloader_adminconf');
			$table->addColumn('ID', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
				'length' => 4,
			]);
			$table->addColumn('KEY', 'string', [
				'notnull' => true,
				'length' => 100,
			]);
			$table->addColumn('VAL', 'string', [
				'notnull' => false,
				'length' => 255,
			]);
			$table->setPrimaryKey(['ID']);
		}

		if (!$schema->hasTable('ocdownloader_personalconf')) {
			$table = $schema->createTable('ocdownloader_personalconf');
			$table->addColumn('ID', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
				'length' => 4,
			]);
			$table->addColumn('UID', 'string', [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('KEY', 'string', [
				'notnull' => true,
				'length' => 100,
			]);
			$table->addColumn('VAL', 'string', [
				'notnull' => false,
				'length' => 255,
			]);
			$table->setPrimaryKey(['ID']);
		}
		return $schema;
	}

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 */
	public function postSchemaChange(IOutput $output, Closure $schemaClosure, array $options) {
	}
}
