

    /**
     * Save order.
     *
     * @Route("/order/{field}/{type}", name="{{ route_name_prefix }}_sort")
     */
    public function sortAction($field, $type)
    {
        $this->setOrder('{{ entity|lower }}', $field, $type);

        return $this->redirect($this->generateUrl('{{ route_name_prefix }}'));
    }

    /**
     * @param string $name   session name
     * @param sting  $field  field name
     * @param sting  $type   sort type ("ASC"/"DESC")
     */
    protected function setOrder($name, $field, $type = 'ASC')
    {
        $this->getRequest()->getSession()->set('sort.' . $name, compact('field', 'type'));
    }

    /**
     * @param  string $name
     * @return string
     */
    protected function getOrder($name)
    {
        $session = $this->getRequest()->getSession();

        return $session->has('sort.' . $name) ? $session->get('sort.' . $name) : null;
    }

    /**
     * @param QueryBuilder $qb
     * @param string       $name
     */
    protected function addQueryBuilderSort(QueryBuilder $qb, $name)
    {
        $alias = current($qb->getDQLPart('from'))->getAlias();
        if (is_array($order = $this->getOrder($name))) {
            $qb->orderBy($alias . '.' . $order['field'], $order['type']);
        }
    }