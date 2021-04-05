<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 9/11/2018
 * Time: 9:48 AM
 */

class MoSAMLPointer
{

    private $content,$anchor_id,$edge,$align,$active,$pointer_name;

    function __construct($header,$body,$anchor_id,$edge,$align,$active,$prefix){

    $this->content = '<h3>'.$header.'</h3>';
    $this->content .= '<p  id="'.$prefix.'" style="font-size: initial;">' .$body . '</p>';
    $this-> anchor_id = $anchor_id;
    $this->edge = $edge;
    $this->align = $align;
    $this->active = $active;
    $this->pointer_name = 'miniorange_admin_pointer_'.$prefix;


    }


     function return_array(){
        return array(
            // The content needs to point to what we created above in the $new_pointer_content variable
            'content' => $this->content,

            // In order for the custom pointer to appear in the right location we need to specify the ID
            // of the element we want it to appear next to
            'anchor_id' => $this->anchor_id,

            // On what edge do we want the pointer to appear. Options are 'top', 'left', 'right', 'bottom'
            'edge' => $this->edge,

            // How do we want out custom pointer to align to the element it is attached to. Options are
            // 'left', 'right', 'center'
            'align' => $this->align,

            // This is how we tell the pointer to be dismissed or not. Make sure that the 'new_items'
            // string matches the string at the beginning of the array item
            'active' => $this->active
        );
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return mixed
     */
    public function getAnchorId()
    {
        return $this->anchor_id;
    }


    /**
     * @return mixed
     */
    public function getEdge()
    {
        return $this->edge;
    }


    /**
     * @return mixed
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param mixed $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return mixed
     */
    public function getPointerName()
    {
        return $this->pointer_name;
    }


}