
    /**
     * Edits an existing {{ entity }} entity.
     *
{% if 'annotation' == format %}
     * @Route("/{id}/update", name="{{ route_name_prefix }}_update")
     * @Method("POST")
     * @Template("{{ bundle }}:{{ entity }}:edit.html.twig")
{% endif %}
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        ${{ entity|lower }} = $em->getRepository('{{ bundle }}:{{ entity }}')->find($id);

        if (!${{ entity|lower }}) {
            throw $this->createNotFoundException('Unable to find {{ entity }} entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new {{ entity_class }}Type(), ${{ entity|lower }});

        if ($editForm->bind($request)->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('{{ route_name_prefix }}_edit', array('id' => $id)));
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
