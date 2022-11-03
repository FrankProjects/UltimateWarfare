<?php

declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\Finder\Finder;

final class Version20180412202011 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $finder = new Finder();
        $finder->in('migrations/Version20180412202011');
        $finder->name('*.sql');
        $finder->sortByName();

        foreach ($finder as $file) {
            $this->connection->exec($file->getContents());
            $this->write('[OK] ' . $file->getFilename());
        }

        $this->addSql('SELECT MAX(version) from migration_versions');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }
}
