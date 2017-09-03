<?php
namespace Consolidation\OutputFormatters\StructuredData;

use Consolidation\OutputFormatters\Options\FormatterOptions;

trait RenderCellCollectionTrait
{

    /** @var RenderCellInterface[] */
    protected $rendererList = [
        RenderCellCollectionInterface::PRIORITY_FIRST => [],
        RenderCellCollectionInterface::PRIORITY_NORMAL => [],
        RenderCellCollectionInterface::PRIORITY_FALLBACK => [],
    ];

    /**
     * Add a renderer
     *
     * @return $this
     */
    public function addRenderer(RenderCellInterface $renderer, $priority = RenderCellCollectionInterface::PRIORITY_NORMAL)
    {
        $this->rendererList[$priority][] = $renderer;
        return $this;
    }

    /**
     * Add a callable as a renderer
     *
     * @return $this
     */
    public function addRendererFunction(callable $rendererFn, $priority = RenderCellCollectionInterface::PRIORITY_NORMAL)
    {
        $renderer = new CallableRenderer($rendererFn);
        return $this->addRenderer($renderer, $priority);
    }

    /**
     * {@inheritdoc}
     */
    public function renderCell($key, $cellData, FormatterOptions $options, $rowData)
    {
        $flattenedRendererList = array_reduce(
            $this->rendererList,
            function ($carry, $item) {
                return array_merge($carry, $item);
            },
            []
        );

        foreach ($flattenedRendererList as $renderer) {
            $cellData = $renderer->renderCell($key, $cellData, $options, $rowData);
            if (is_string($cellData)) {
                return $cellData;
            }
        }
        return $cellData;
    }
}
