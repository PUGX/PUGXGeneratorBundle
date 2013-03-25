<?php

namespace {{ namespace }}\Form\Type{{ entity_namespace ? '\\' ~ entity_namespace : '' }};

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class {{ form_class }} extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

        {%- for field, metadata in fields %}
            {%- if metadata.type == 'relation_many' %}
                ->add('{{ field }}', 'filter_entity', array('class' => '{{ namespace }}\Entity\{{ field }}')) /* XXX adapt */
            {%- elseif metadata.type == 'boolean' %}
                ->add('{{ field }}', 'filter_boolean')
            {%- elseif field != 'id' %}
                ->add('{{ field }}', 'filter_text')
            {% endif %}
        {%- endfor %}

        ;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'        => '{{ namespace }}\Entity{{ entity_namespace ? '\\' ~ entity_namespace : '' }}\{{ entity_class }}',
            'csrf_protection'   => false,
            'validation_groups' => array('filter'),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return '{{ entity_class|lower }}_filter';
    }
}
