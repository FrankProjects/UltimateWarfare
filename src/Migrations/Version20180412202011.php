<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\Finder\Finder;

/**
 * Class Version20180412202011
 * @package DoctrineMigrations
 */
class Version20180412202011 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $finder = new Finder();
        $finder->in('src/Migrations/Version20180412202011');
        $finder->name('*.sql');
        $finder->sortByName();

        foreach ($finder as $file) {
            $this->connection->exec($file->getContents());
            $this->write('[OK] ' . $file->getFilename());
        }

        $this->addSql('SELECT MAX(version) from migration_versions');
    }

    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }
}
