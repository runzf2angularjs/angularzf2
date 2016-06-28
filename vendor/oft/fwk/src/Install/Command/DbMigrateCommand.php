<?php
/**
 * Copyright (C) 2015 Orange
 *
 * This software is confidential and proprietary information of Orange.
 * You shall not disclose such Confidential Information and shall use it only
 * in accordance with the terms of the agreement you entered into.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * If you are Orange employee you shall use this software in accordance with
 * the Orange Source Charter (http://opensource.itn.ftgroup/index.php/Orange_Source).
 */

namespace Oft\Install\Command;

use Doctrine\DBAL\Migrations\Tools\Console\Command\MigrateCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DbMigrateCommand extends MigrateCommand
{

    /**
     * {@inheritdoc}
     */
    protected function getMigrationConfiguration(InputInterface $input, OutputInterface $output)
    {
        return $this->getApplication()->getApp()
            ->get('DoctrineMigrations');
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('db:migrate');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->showVersions($input, $output);

        if (parent::execute($input, $output) !== 1) {
            $this->showVersions($input, $output);
        }
    }

    protected function showVersions(InputInterface $input, OutputInterface $output)
    {
        $configuration = $this->getMigrationConfiguration($input, $output);

        $migrations = $configuration->getMigrations();

        if (!count($migrations)) {
            $output->writeln("\n <info>==</info> No migration script found\n");
            return;
        }

        $output->writeln("\n <info>==</info> Migration Versions\n");
        $migratedVersions = $configuration->getMigratedVersions();
        foreach ($migrations as $version) {
            $isMigrated = in_array($version->getVersion(), $migratedVersions);
            $status = $isMigrated ? '<info>migrated</info>' : '<error>not migrated</error>';
            $output->writeln('    <comment>>></comment> ' . $configuration->formatVersion($version->getVersion()) . ' (<comment>' . $version->getVersion() . '</comment>)' . str_repeat(' ', 30 - strlen($version->getVersion())) . $status);
        }
    }

}
