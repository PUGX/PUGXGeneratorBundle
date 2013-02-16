
    /**
     * Deletes a {{ entity }} entity.
     *
{% if 'annotation' == format %}
     * @Route("/{id}/delete", name="{{ route_name_prefix }}_delete")
     * @Method("POST")
{% endif %}
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            ${{ entity|lower }} = $em->getRepository('{{ bundle }}:{{ entity }}')->find($id);

            if (!${{ entity|lower }}) {
                throw $this->createNotFoundException('Unable to find {{ entity }} entity.');
            }

            $em->remove(${{ entity|lower }});
            $em->flush();
        }

        return $this->redirect($this->generateUrl('{{ route_name_prefix }}'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }