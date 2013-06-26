
    /**
     * Lists all {{ entity }} entities.
     *
{% if 'annotation' == format %}
     * @Route("/", name="{{ route_name_prefix }}")
     * @Template()
{% endif %}
     */
    public function indexAction({% if usePaginator %}Request $request{% endif %})
    {
        $em = $this->getDoctrine()->getManager();
{% if withFilter %}
        $form = $this->createForm(new {{ entity_class }}FilterType());
        if (!is_null($response = $this->saveFilter($form, '{{ entity|lower }}', '{{ route_name_prefix }}'))) {
            return $response;
        }
{% endif %}
{% if usePaginator %}

        {% if withFilter -%}

        $qb = $em->getRepository('{{ bundle }}:{{ entity }}')->createQueryBuilder('{{ entity|lower|slice(0, 1) }}');
        $paginator = $this->filter($form, $qb, '{{ entity|lower }}');
        {% else -%}

        $q = $em->getRepository('{{ bundle }}:{{ entity }}')->createQueryBuilder('{{ entity|lower|slice(0, 1) }}')->getQuery();
        $paginator = $this->get('knp_paginator')->paginate($q, $request->query->get('page', 1), 20);
        {% endif %}

{% else -%}
        {# TODO qui non consideriamo withSort #}
        $entities = $em->getRepository('{{ bundle }}:{{ entity }}')->findAll();
{% endif %}

{% if 'annotation' == format %}
        return array(
{% if withFilter %}
            'form' => $form->createView(),
{% endif %}
{% if usePaginator %}
            'paginator' => $paginator,
{% else %}
            'entities' => $entities,
{% endif %}
        );
{% else %}
        return $this->render('{{ bundle }}:{{ entity|replace({'\\': '/'}) }}:index.html.twig', array(
{% if usePaginator %}
            'paginator' => $paginator,
{% else %}
            'entities' => $entities,
{% endif %}
        ));
{% endif %}
    }
