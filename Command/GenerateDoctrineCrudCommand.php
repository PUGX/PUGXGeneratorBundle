<?php

namespace PUGX\GeneratorBundle\Command;

use PUGX\GeneratorBundle\Generator\DoctrineCrudGenerator;
use PUGX\GeneratorBundle\Generator\DoctrineFormGenerator;
use Sensio\Bundle\GeneratorBundle\Command\GenerateDoctrineCrudCommand as BaseCommand;
use Sensio\Bundle\GeneratorBundle\Command\Validators;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generates a CRUD for a Doctrine entity.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Leonardo Proietti <leonardo.proietti@gmail.com>
 * @author Massimiliano Arione <garakkio@gmail.com>
 */
class GenerateDoctrineCrudCommand extends BaseCommand
{
    private $generator;
    private $formGenerator;
    private $filterGenerator;

    protected function createGenerator($bundle = null)
    {
        return new DoctrineCrudGenerator($this->getContainer()->get('filesystem'));
    }

    protected function configure()
    {
        $this
            ->setDefinition(array(
                new InputOption('entity', '', InputOption::VALUE_REQUIRED, 'The entity class name to initialize (shortcut notation)'),
                new InputOption('layout', '', InputOption::VALUE_REQUIRED, 'The layout to use for templates', 'TwigBundle::layout.html.twig'),
                new InputOption('body-block', '', InputOption::VALUE_REQUIRED, 'The name of "body" block in your layout', 'body'),
                new InputOption('route-prefix', '', InputOption::VALUE_REQUIRED, 'The route prefix'),
                new InputOption('with-write', '', InputOption::VALUE_NONE, 'Whether or not to generate create, new and delete actions'),
                new InputOption('overwrite', '', InputOption::VALUE_NONE, 'Do not stop the generation if crud controller already exist, thus overwriting all generated files'),
                new InputOption('format', '', InputOption::VALUE_REQUIRED, 'Use the format for configuration files (php, xml, yml, or annotation)', 'annotation'),
                new InputOption('use-paginator', '', InputOption::VALUE_NONE,'Whether or not to use paginator'),
                new InputOption('theme', '', InputOption::VALUE_OPTIONAL, 'A possible theme to use in forms'),
                new InputOption('with-filter', '', InputOption::VALUE_NONE, 'Whether or not to add filter'),
                new InputOption('with-sort', '', InputOption::VALUE_NONE, 'Whether or not to add sorting'),
            ))
            ->setDescription('Generates a CRUD based on a Doctrine entity')
            ->setHelp(<<<EOT
The <info>pugx:generate:crud</info> command generates a CRUD based on a Doctrine entity.

The default command only generates the list and show actions.

<info>php app/console pugx:generate:crud --entity=AcmeBlogBundle:Post --route-prefix=post_admin</info>

Using the --with-write option allows to generate the new, edit and delete actions.

<info>php app/console pugx:generate:crud --entity=AcmeBlogBundle:Post --route-prefix=post_admin --with-write</info>

Using the --use-paginator option allows to generate list action with paginator.

Using the --with-filter option allows to generate list action with filter.

Using the --with-sort option allows to generate list action with sorting.
EOT
            )
            ->setName('pugx:generate:crud')
            ->setAliases(array('generate:pugx:crud'))
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getDialogHelper();

        if ($input->isInteractive()) {
            if (!$dialog->askConfirmation($output, $dialog->getQuestion('Do you confirm generation', 'yes', '?'), true)) {
                $output->writeln('<error>Command aborted</error>');

                return 1;
            }
        }

        $entity = Validators::validateEntityName($input->getOption('entity'));
        list($bundle, $entity) = $this->parseShortcutNotation($entity);

        $format = Validators::validateFormat($input->getOption('format'));
        $prefix = $this->getRoutePrefix($input, $entity);
        $withWrite = $input->getOption('with-write');
        $forceOverwrite = $input->getOption('overwrite');
        $layout = $input->getOption('layout');  // TODO validate
        $bodyBlock = $input->getOption('body-block');  // TODO validate
        $usePaginator = $input->getOption('use-paginator');
        $theme = $input->getOption('theme');  // TODO validate
        $withFilter = $input->getOption('with-filter');  // TODO validate
        $withSort = $input->getOption('with-sort');  // TODO validate

        if ($withFilter && !$usePaginator) {
            throw new \RuntimeException(sprintf('Cannot use filter without paginator.'));
        }

        $dialog->writeSection($output, 'CRUD generation');  // TODO overwrite interaction

        $entityClass = $this->getContainer()->get('doctrine')->getAliasNamespace($bundle).'\\'.$entity;
        $metadata    = $this->getEntityMetadata($entityClass);
        $bundle      = $this->getContainer()->get('kernel')->getBundle($bundle);

        $generator = $this->getGenerator($bundle);
        $generator->generate($bundle, $entity, $metadata[0], $format, $prefix, $withWrite, $forceOverwrite, $layout, $bodyBlock, $usePaginator, $theme, $withFilter, $withSort);

        $output->writeln('Generating the CRUD code: <info>OK</info>');

        $errors = array();
        $runner = $dialog->getRunner($output, $errors);

        // form
        if ($withWrite) {
            $this->generateForm($bundle, $entity, $metadata);
            $output->writeln('Generating the Form code: <info>OK</info>');
        }

        // filter form
        if ($withFilter) {
            $this->generateFilter($bundle, $entity, $metadata);
            $output->writeln('Generating the Filter code: <info>OK</info>');
        }

        // routing
        if ('annotation' != $format) {
            $runner($this->updateRouting($dialog, $input, $output, $bundle, $format, $entity, $prefix));
        }

        $dialog->writeGeneratorSummary($output, $errors);
    }

    protected function getFormGenerator($bundle = null)
    {
        if (null === $this->formGenerator) {
            $this->formGenerator = new DoctrineFormGenerator($this->getContainer()->get('filesystem'));
            $this->formGenerator->setSkeletonDirs($this->getSkeletonDirs($bundle));
        }

        return $this->formGenerator;
    }

    protected function getFilterGenerator()
    {
        if (null === $this->filterGenerator) {
            $this->filterGenerator = new DoctrineFormGenerator($this->getContainer()->get('filesystem'), __DIR__.'/../Resources/skeleton/filter', null);
        }

        return $this->filterGenerator;
    }

    /**
     * Tries to generate forms if they don't exist yet and if we need write operations on entities.
     */
    protected function generateForm($bundle, $entity, $metadata)
    {
        try {
            $this->getFormGenerator($bundle)->generate($bundle, $entity, $metadata[0]);
        } catch (\RuntimeException $e) {
            // form already exists
        }
    }

    /**
     * Tries to generate filter forms if they don't exist yet
     */
    protected function generateFilter($bundle, $entity, $metadata)
    {
        try {
            $this->getFilterGenerator()->generateFilter($bundle, $entity, $metadata[0]);
        } catch (\RuntimeException $e ) {
            // form already exists
        }
    }

    /**
     * adds this bundles skeleton dirs to the beginning of skeletonDirs array
     *
     * @param null $bundle
     * @return array
     */
    protected function getSkeletonDirs($bundle = null)
    {
        $baseSkeletonDirs = parent::getSkeletonDirs($bundle);

        $skeletonDirs = array();

        if (isset($bundle) && is_dir($dir = $bundle->getPath().'/Resources/PUGXGeneratorBundle/skeleton')) {
            $skeletonDirs[] = $dir;
        }

        if (is_dir($dir = $this->getContainer()->get('kernel')->getRootdir().'/Resources/PUGXGeneratorBundle/skeleton')) {
            $skeletonDirs[] = $dir;
        }

        $skeletonDirs[] = __DIR__.'/../Resources/skeleton';
        $skeletonDirs[] = __DIR__.'/../Resources';

        return array_merge($skeletonDirs, $baseSkeletonDirs);
    }
}
