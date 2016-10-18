<?php

namespace PUGX\GeneratorBundle\Generator;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Sensio\Bundle\GeneratorBundle\Generator\Generator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

/**
 * Generates a fixtures class for Doctrine ORM.
 *
 * @author Massimiliano Arione <garakkio@gmail.com>
 */
class DoctrineFixturesGenerator extends Generator
{
    protected $filesystem;

    /**
     * Constructor.
     *
     * @param Filesystem $filesystem A Filesystem instance
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * Generate the fixtures class.
     *
     * @param BundleInterface   $bundle   A bundle object
     * @param string            $entity   The entity relative class name
     * @param ClassMetadataInfo $metadata The entity class metadata
     * @parma integer           $num        The number of fixtures to generate
     *
     * @throws \RuntimeException
     */
    public function generate(BundleInterface $bundle, $entity, ClassMetadataInfo $metadata, $num = 1)
    {
        $parts = explode('\\', $entity);
        $entityClass = array_pop($parts);

        $dir = $bundle->getPath().'/DataFixtures/ORM/';
        $this->filesystem->mkdir($dir);

        $this->renderFile('fixtures/DataFixtures.php.twig', $dir.$entityClass.'Data.php', [
            'namespace' => $bundle->getNamespace(),
            'bundle' => $bundle->getName(),
            'entity' => $entity,
            'entity_class' => $entityClass,
            'fields' => $this->getFieldsFromMetadata($metadata),
            'num' => $num,
        ]);
    }

    /**
     * Returns an array of fields. Fields can be both column fields and
     * association fields.
     *
     * @param ClassMetadataInfo $metadata
     *
     * @return array $fields
     */
    private function getFieldsFromMetadata(ClassMetadataInfo $metadata)
    {
        $fields = (array) $metadata->fieldMappings;

        foreach ($metadata->associationMappings as $fieldName => $relation) {
            if ($relation['type'] !== ClassMetadataInfo::ONE_TO_MANY) {
                if ($relation['type'] === ClassMetadataInfo::MANY_TO_MANY) {
                    $fields[$fieldName] = ['type' => 'relation_many', 'entity' => $relation['targetEntity']];
                } else {
                    $fields[$fieldName] = ['type' => 'relation', 'entity' => $relation['targetEntity']];
                }
            }
        }

        return $fields;
    }
}
