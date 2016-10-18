<?php

namespace PUGX\GeneratorBundle\Command;

use Doctrine\ORM\ORMException;
use PUGX\GeneratorBundle\Generator\DoctrineCrudGenerator;
use PUGX\GeneratorBundle\Generator\DoctrineFixturesGenerator;
use PUGX\GeneratorBundle\Generator\DoctrineFormGenerator;
use Sensio\Bundle\GeneratorBundle\Command\GenerateDoctrineCrudCommand as BaseCommand;
use Sensio\Bundle\GeneratorBundle\Command\Validators;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

/**
 * Generates a CRUD for a Doctrine entity.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Leonardo Proietti <leonardo.proietti@gmail.com>
 * @author Massimiliano Arione <garakkio@gmail.com>
 * @author Eugenio Pombi <euxpom@gmail.com>
 */
class GenerateDoctrineCrudCommand extends BaseCommand
{
    private $formGenerator;
    private $filterGenerator;
    private $fixturesGenerator;

    protected function createGenerator($bundle = null)
    {
        return new DoctrineCrudGenerator(
            $this->getContainer()->get('filesystem'),
            $this->getContainer()->getParameter('kernel.root_dir')
        );
    }

    protected function configure()
    {
        $this
            ->setDefinition([
                new InputOption('entity', '', InputOption::VALUE_REQUIRED, 'The entity class name to initialize (shortcut notation)'),
                new InputOption('layout', '', InputOption::VALUE_REQUIRED, 'The layout to use for templates', 'base.html.twig'),
                new InputOption('body-block', '', InputOption::VALUE_REQUIRED, 'The name of "body" block in your layout', 'body'),
                new InputOption('route-prefix', '', InputOption::VALUE_REQUIRED, 'The route prefix'),
                new InputOption('with-write', '', InputOption::VALUE_NONE, 'Whether or not to generate create, new and delete actions'),
                new InputOption('overwrite', '', InputOption::VALUE_NONE, 'Do not stop the generation if crud controller already exist, thus overwriting all generated files'),
                new InputOption('format', '', InputOption::VALUE_REQUIRED, 'Use the format for configuration files (php, xml, yml, or annotation)', 'annotation'),
                new InputOption('use-paginator', '', InputOption::VALUE_NONE, 'Whether or not to use paginator'),
                new InputOption('theme', '', InputOption::VALUE_OPTIONAL, 'A possible theme to use in forms'),
                new InputOption('with-filter', '', InputOption::VALUE_NONE, 'Whether or not to add filter'),
                new InputOption('with-sort', '', InputOption::VALUE_NONE, 'Whether or not to add sorting'),
                new InputOption('fixtures', '', InputOption::VALUE_OPTIONAL, 'Possibile number of fixtures to generate', 0),
            ])
            ->setDescription('Generates a CRUD based on a Doctrine entity')
            ->setHelp(<<<'EOT'
The <info>%command.name%</info> command generates a CRUD based on a Doctrine entity.

The default command only generates the <comment>list</comment> and <comment>show</comment> actions.

<info>php %command.full_name% --entity=AppBundle:Post --route-prefix=post_admin</info>

Using the --with-write option allows to generate the <comment>new</comment>, <comment>edit</comment> and <comment>delete</comment> actions.

<info>php %command.full_name% --entity=AppBundle:Post --route-prefix=post_admin --with-write</info>

Using the --use-paginator option allows to generate <comment>list</comment> action with paginator.

Using the --with-filter option allows to generate <comment>list</comment> action with filter.

Using the --with-sort option allows to generate <comment>list</comment> action with sorting.
EOT
            )
            ->setName('pugx:generate:crud')
            ->setAliases(['generate:pugx:crud']);
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();
        if ($input->isInteractive()) {
            $question = new Question($questionHelper->getQuestion('Do you confirm generation', 'yes', '?'), true);
            if (!$questionHelper->ask($input, $output, $question)) {
                $output->writeln('<error>Command aborted</error>');

                return 1;
            }
        }

        $entity = Validators::validateEntityName($input->getOption('entity'));
        list($alias, $bundle, $entity) = $this->parseShortcutNotation($entity);

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
        $fixtures = $input->getOption('fixtures');  // TODO validate

        if ($withFilter && !$usePaginator) {
            throw new \RuntimeException(sprintf('Cannot use filter without paginator.'));
        }

        $questionHelper->writeSection($output, 'CRUD generation');

        // see https://github.com/sensiolabs/SensioGeneratorBundle/issues/277
        try {
            $entityClass = $this->getContainer()->get('doctrine')->getAliasNamespace($alias).'\\'.$entity;
        } catch (ORMException $e) {
            $entityClass = $alias.'\\'.$entity;
        }
        $metadata = $this->getEntityMetadata($entityClass);
        $bundle = $this->getContainer()->get('kernel')->getBundle($bundle);

        $generator = $this->getGenerator($bundle);
        $generator->generate($bundle, $entity, $metadata[0], $format, $prefix, $withWrite, $forceOverwrite, $layout, $bodyBlock, $usePaginator, $theme, $withFilter, $withSort);

        $output->writeln('Generating the CRUD code: <info>OK</info>');

        $errors = [];
        $runner = $questionHelper->getRunner($output, $errors);

        // form
        if ($withWrite) {
            $this->doGenerateForm($bundle, $entity, $metadata);
            $output->writeln('Generating the Form code: <info>OK</info>');
        }

        // filter form
        if ($withFilter) {
            $this->doGenerateFilter($bundle, $entity, $metadata);
            $output->writeln('Generating the Filter code: <info>OK</info>');
        }

        // routing
        if ('annotation' != $format) {
            $runner($this->updateRouting($questionHelper, $input, $output, $bundle, $format, $entity, $prefix));
        }

        // fixtures
        if ($fixtures > 0) {
            $this->doGenerateFixtures($bundle, $entity, $metadata, $fixtures);
            $output->writeln(sprintf('Generating %d fixture%s: <info>OK</info>', $fixtures, $fixtures > 1 ? 's' : ''));
        }

        $questionHelper->writeGeneratorSummary($output, $errors);
    }

    protected function getFormGenerator($bundle = null)
    {
        if (null === $this->formGenerator) {
            $this->formGenerator = new DoctrineFormGenerator($this->getContainer()->get('filesystem'));
            $this->formGenerator->setSkeletonDirs($this->getSkeletonDirs($bundle));
        }

        return $this->formGenerator;
    }

    protected function getFilterGenerator($bundle = null)
    {
        if (null === $this->filterGenerator) {
            $this->filterGenerator = new DoctrineFormGenerator($this->getContainer()->get('filesystem'));
            $this->filterGenerator->setSkeletonDirs($this->getSkeletonDirs($bundle));
        }

        return $this->filterGenerator;
    }

    /**
     * @param string $bundle
     *
     * @return DoctrineFixturesGenerator
     */
    protected function getFixturesGenerator($bundle = null)
    {
        if (null === $this->fixturesGenerator) {
            $this->fixturesGenerator = new DoctrineFixturesGenerator($this->getContainer()->get('filesystem'));
            $this->fixturesGenerator->setSkeletonDirs($this->getSkeletonDirs($bundle));
        }

        return $this->fixturesGenerator;
    }

    protected function doGenerateForm($bundle, $entity, $metadata)
    {
        try {
            $this->getFormGenerator($bundle)->generate($bundle, $entity, $metadata[0]);
        } catch (\RuntimeException $e) {
            // form already exists
        }
    }

    protected function doGenerateFilter($bundle, $entity, $metadata)
    {
        try {
            $this->getFilterGenerator($bundle)->generateFilter($bundle, $entity, $metadata[0]);
        } catch (\RuntimeException $e) {
            // form already exists
        }
    }

    /**
     * Generate fixtures.
     *
     * @param string $bundle
     * @param string $entity
     * @param array  $metadata array of \Doctrine\ORM\Mapping\ClassMetadata objects
     * @param int    $num
     */
    protected function doGenerateFixtures($bundle, $entity, $metadata, $num = 1)
    {
        $this->getFixturesGenerator($bundle)->generate($bundle, $entity, $metadata[0], $num);
    }

    /**
     * add this bundle skeleton dirs to the beginning of the parent skeletonDirs array.
     *
     * @param BundleInterface $bundle
     *
     * @return array
     */
    protected function getSkeletonDirs(BundleInterface $bundle = null)
    {
        $baseSkeletonDirs = parent::getSkeletonDirs($bundle);

        $skeletonDirs = [];

        if (isset($bundle) && is_dir($dir = $bundle->getPath().'/Resources/PUGXGeneratorBundle/skeleton')) {
            $skeletonDirs[] = $dir;
        }

        if (is_dir($dir = $this->getContainer()->get('kernel')->getRootdir().'/Resources/PUGXGeneratorBundle/skeleton')) {
            $skeletonDirs[] = $dir;
        }

        $bundlesDirectories = $this->getContainer()->get('kernel')->locateResource('@PUGXGeneratorBundle/Resources/skeleton', null, false);

        $skeletonDirs = array_merge($skeletonDirs, $bundlesDirectories);
        $skeletonDirs[] = __DIR__.'/../Resources';

        return array_merge($skeletonDirs, $baseSkeletonDirs);
    }

    /**
     * Override "interact" method to ask for adding parameters.
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();
        $questionHelper->writeSection($output, 'Welcome to the PUGX CRUD generator');

        // namespace
        $output->writeln([
            '',
            'This command helps you generate CRUD controllers and templates.',
            '',
            'First, you need to give the entity for which you want to generate a CRUD.',
            'You can give an entity that does not exist yet and the wizard will help',
            'you defining it.',
            '',
            'You must use the shortcut notation like <comment>AcmeBlogBundle:Post</comment>.',
            '',
        ]);

        $question = new Question($questionHelper->getQuestion('The Entity shortcut name', $input->getOption('entity')), $input->getOption('entity'));
        $question->setValidator(['Sensio\Bundle\GeneratorBundle\Command\Validators', 'validateEntityName']);
        $entity = $questionHelper->ask($input, $output, $question);
        $input->setOption('entity', $entity);
        list($alias, $bundle, $entity) = $this->parseShortcutNotation($entity);

        // see https://github.com/sensiolabs/SensioGeneratorBundle/issues/277
        // Entity exists?
        try {
            $entityClass = $this->getContainer()->get('doctrine')->getAliasNamespace($alias).'\\'.$entity;
        } catch (ORMException $e) {
            $entityClass = $alias.'\\'.$entity;
        }
        $this->getEntityMetadata($entityClass);

        // layout
        $output->writeln([
            '',
            'Select a layout. Example: <comment>AppBundle::layout.html.twig</comment>',
            '',
        ]);
        // TODO add validator
        $question = new Question($questionHelper->getQuestion('Layout name', $input->getOption('layout')), $input->getOption('layout'));
        $layout = $questionHelper->ask($input, $output, $question);
        $input->setOption('layout', $layout);

        // paginator?
        $usePaginator = $input->getOption('use-paginator') ?: false;
        $output->writeln([
            '',
            'By default, the generator creates an index action with list of all entites.',
            'You can also ask it to generate a paginator. Please notice that <comment>KnpPaginatorBundle</comment> is required.',
            '',
        ]);
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion($questionHelper->getQuestion('Do you want a paginator', $usePaginator ? 'yes' : 'no', '?', $usePaginator),
                                             $usePaginator);
        $input->setOption('use-paginator', $helper->ask($input, $output, $question) != false);

        // filter?
        $withFilter = $input->getOption('with-filter') ?: false;
        $output->writeln([
            '',
            'You can add a filter to generated index. Please notice that <comment>LexikFormFilterBundle </comment> is required.',
            '',
        ]);
        $question = new ConfirmationQuestion($questionHelper->getQuestion('Do you want filter', $withFilter ? 'yes' : 'no', '?', $withFilter),
                                             $withFilter);
        $input->setOption('with-filter', $helper->ask($input, $output, $question) != false);

        // sort?
        $withSort = $input->getOption('with-sort') ?: false;
        $output->writeln([
            '',
            'You can add sort links to columns of generated index.',
            '',
        ]);
        $question = new ConfirmationQuestion($questionHelper->getQuestion('Do you want sort', $withSort ? 'yes' : 'no', '?', $withSort),
                                             $withSort);
        $input->setOption('with-sort', $helper->ask($input, $output, $question) != false);

        // write?
        $withWrite = $input->getOption('with-write') ?: false;
        $output->writeln([
            '',
            'By default, the generator creates two actions: list and show.',
            'You can also ask it to generate "write" actions: new, update, and delete.',
            '',
        ]);
        $question = new ConfirmationQuestion($questionHelper->getQuestion('Do you want to generate the "write" actions', $withWrite ? 'yes' : 'no', '?', $withWrite), $withWrite);
        $input->setOption('with-write', $helper->ask($input, $output, $question) != false);

        // format
        $format = $input->getOption('format');
        $output->writeln([
            '',
            'Determine the format to use for the generated CRUD.',
            '',
        ]);
        $question = new Question($questionHelper->getQuestion('Configuration format (yml, xml, php, or annotation)', $format), $format);
        $question->setValidator(['Sensio\Bundle\GeneratorBundle\Command\Validators', 'validateFormat']);
        $format = $questionHelper->ask($input, $output, $question);
        $input->setOption('format', $format);

        // route prefix
        $prefix = $this->getRoutePrefix($input, $entity);
        $output->writeln([
            '',
            'Determine the routes prefix (all the routes will be "mounted" under this',
            'prefix: /prefix/, /prefix/new, ...).',
            '',
        ]);
        $question = new Question($questionHelper->getQuestion('Routes prefix', '/'.$prefix), '/'.$prefix);
        $prefix = $questionHelper->ask($input, $output, $question);
        $input->setOption('route-prefix', $prefix);

        // TODO fixtures...

        // summary
        $output->writeln([
            '',
            $this->getHelper('formatter')->formatBlock('Summary before generation', 'bg=blue;fg=white', true),
            '',
            sprintf('You are going to generate a CRUD controller for "<info>%s:%s</info>"', $bundle, $entity),
            sprintf('using the "<info>%s</info>" format.', $format),
            '',
        ]);
    }

    /**
     * Original method is not working with aliases set by configuration.
     *
     * See https://github.com/sensiolabs/SensioGeneratorBundle/issues/277
     */
    protected function parseShortcutNotation($shortcut)
    {
        $entity = str_replace('/', '\\', $shortcut);

        if (false === $pos = strpos($entity, ':')) {
            throw new \InvalidArgumentException(sprintf('The entity name must contain a : ("%s" given, expecting something like AppBundle:Blog/Post)', $entity));
        }
        $bundle = substr($entity, 0, $pos);

        $bundles = $this->getContainer()->get('kernel')->getBundles();
        if (array_key_exists($bundle, $bundles)) {
            return [$bundle, $bundle, substr($entity, $pos + 1)];
        }

        try {
            $alias = $this->getContainer()->get('doctrine')->getAliasNamespace($bundle);
            $bundleName = str_replace(['\\', 'Entity'], '', $alias);
        } catch (ORMException $e) {
            $alias = $bundle;
            $bundleName = $bundle;
        }

        return [$alias, $bundleName, substr($entity, $pos + 1)];
    }
}
