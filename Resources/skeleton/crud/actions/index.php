
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

{% if usePaginator %}
        $q = $em->getRepository('{{ bundle }}:{{ entity }}')->createQueryBuilder('{{ entity|lower|slice(0, 1) }}')->getQuery();
        $paginator = $this->get('knp_paginator')->paginate($q, $request->query->get('page', 1), 20);
{% else %}
        $entities = $em->getRepository('{{ bundle }}:{{ entity }}')->findAll();
{% endif %}

{% if 'annotation' == format %}
        return array(
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
