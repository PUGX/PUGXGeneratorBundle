
    /**
     * Edits an existing {{ entity }} entity.
     *
{% if 'annotation' == format %}
     * @Route("/{id}/update", name="{{ route_name_prefix }}_update")
     * @Method("POST")
     * @Template("{{ bundle }}:{{ entity }}:edit.html.twig")
{% endif %}
     */
    public function updateAction({{ entity }} ${{ entity|lower }})
    {
        $em = $this->getDoctrine()->getManager();
        $deleteForm = $this->createDeleteForm(${{ entity|lower }}->getId());
        $editForm = $this->createForm(new {{ entity_class }}Type(), ${{ entity|lower }});
        if ($editForm->bind($this->getRequest())->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('{{ route_name_prefix }}_edit', array('id' => ${{ entity|lower }}->getId())));
        }

{% if 'annotation' == format %}
        return array(
            '{{ entity|lower }}' => ${{ entity|lower }},
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
{% else %}
        return $this->render('{{ bundle }}:{{ entity|replace({'\\': '/'}) }}:edit.html.twig', array(
            '{{ entity|lower }}' => ${{ entity|lower }},
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
{% endif %}
    }
