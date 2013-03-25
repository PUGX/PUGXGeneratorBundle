
    /**
     * Save filters
     *
     * @param  FormInterface $form
     * @param  string        $name        route/entity name
     * @param  string        $route       route name, if different from entity name
     * @param  array         $params      possible route parameters
     * @return Response
     */
    protected function saveFilter(FormInterface $form, $name, $route = null, array $params = null)
    {
        $request = $this->getRequest();
        $url = is_null($route) ? $this->generateUrl($name) : $this->generateUrl($route, $params);
        if ($request->query->has('submit-filter')) {
            if ($form->bind($request)->isValid()) {
                $this->setFilter($form->getData(), $name);

                return $this->redirect($url);
            }
        } elseif ($request->query->has('reset-filter')) {
            $this->resetFilter($name);

            return $this->redirect($url);
        }
    }

    /**
     * Filter form
     *
     * @param  FormInterface  $form
     * @param  QueryBuilder   $qb
     * @return SlidingPagination
     */
    protected function filter(FormInterface $form, QueryBuilder $qb, $name)
    {
        if (!is_null($values = $this->getFilter($name))) {
            $v = array_map(array($this, 'convertEntity'), $values);
            if ($form->bind($v)->isValid()) {
                $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($form, $qb);
            }
        }

        return $this->get('knp_paginator')->paginate($qb->getQuery(), $this->getRequest()->query->get('page', 1), 20);
    }

    /**
     * Save filters in session, in a fieldName/fieldValue array
     *
     * @param mixed  $entity
     * @param string $name
     */
    protected function setFilter($entity, $name)
    {
        $values = array();
        $array = (array) $entity;
        foreach ($array as $property => $value) {
            if (is_null($value) || ($value instanceof ArrayCollection && count($value) == 0)) {
                continue;
            }
            if (is_object($value) && is_callable($value, 'getId'))
            {
                $value = $value->getId();
            }
            // workaround: keys are like '�*�id'
            $values[substr($property, 3)] = $value;
        }

        $this->getRequest()->getSession()->set('filter.' . $name, $values);
    }

    /**
     * Get filters from session
     *
     * @param  string $name
     * @return array
     */
    protected function getFilter($name)
    {
        return $this->getRequest()->getSession()->get('filter.' . $name);
    }

    /**
     * Reset filters (delete them from session)
     *
     * @param  string $name
     */
    protected function resetFilter($name)
    {
        $this->getRequest()->getSession()->set('filter.' . $name, null);
    }

    /**
     * Converts entities to their ids
     *
     * @param  mixed $value
     * @return mixed
     * @see    filter()
     */
    private function convertEntity($value)
    {
        if (is_object($value) && get_class($value) == 'Doctrine\Common\Collections\ArrayCollection') {
            return $value->toArray();
        }
        if (is_object($value) && is_callable($value, 'getId')) {
            return $value->toArray();
        }

        return $value;
    }
