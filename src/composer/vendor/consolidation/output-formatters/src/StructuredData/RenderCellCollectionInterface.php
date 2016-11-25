<?php
namespace Consolidation\OutputFormatters\StructuredData;

interface RenderCellCollectionInterface extends RenderCellInterface
{
    const PRIORITY_FIRST = 'first';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_FALLBACK = 'fallback';

    /**
     * Add a renderer
     *
     * @return $this
     */
    public function addRenderer(RenderCellInterface $renderer);
}
