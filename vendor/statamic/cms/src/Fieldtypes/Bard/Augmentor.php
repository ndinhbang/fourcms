<?php

namespace Statamic\Fieldtypes\Bard;

use ProseMirrorToHtml\Marks\Link as DefaultLinkMark;
use ProseMirrorToHtml\Nodes\Image as DefaultImageNode;
use ProseMirrorToHtml\Renderer;
use Statamic\Fields\Field;
use Statamic\Fields\Value;
use Statamic\Fields\Values;
use Statamic\Fieldtypes\Bard\ImageNode as CustomImageNode;
use Statamic\Fieldtypes\Bard\LinkMark as CustomLinkMark;
use Statamic\Fieldtypes\Text;
use Statamic\Support\Arr;

class Augmentor
{
    protected $fieldtype;
    protected $sets = [];
    protected $includeDisabledSets = false;
    protected $augmentSets = true;
    protected $withStatamicImageUrls = false;

    protected static $customMarks = [];
    protected static $customNodes = [];
    protected static $replaceMarks = [];
    protected static $replaceNodes = [];

    public function __construct($fieldtype)
    {
        $this->fieldtype = $fieldtype;
    }

    public function withStatamicImageUrls()
    {
        $this->withStatamicImageUrls = true;

        return $this;
    }

    public function augment($value, $shallow = false)
    {
        $hasSets = (bool) $this->fieldtype->config('sets');

        if (! $value) {
            return $hasSets ? [] : null;
        }

        if (is_string($value)) {
            return $value;
        }

        if (! $hasSets) {
            return $this->convertToHtml($value);
        }

        if (! $this->includeDisabledSets) {
            $value = $this->removeDisabledSets($value);
        }

        $value = $this->addSetIndexes($value);
        $value = $this->convertToHtml($value);
        $value = $this->convertToSets($value);

        if ($this->augmentSets) {
            $value = $this->augmentSets($value, $shallow);
        }

        return collect($value)->mapInto(Values::class)->all();
    }

    public function withDisabledSets()
    {
        $this->includeDisabledSets = true;

        return $this;
    }

    public function withoutAugmentingSets()
    {
        $this->augmentSets = false;

        return $this;
    }

    protected function removeDisabledSets($value)
    {
        return collect($value)->reject(function ($value) {
            return $value['type'] === 'set'
                && Arr::get($value, 'attrs.enabled', true) === false;
        })->values();
    }

    protected function addSetIndexes($value)
    {
        return collect($value)->map(function ($value, $index) {
            if ($value['type'] == 'set') {
                $this->sets[$index] = $value['attrs']['values'];
                $value['index'] = 'index-'.$index;
            }

            return $value;
        })->all();
    }

    public function convertToHtml($value)
    {
        $customImageNode = $this->withStatamicImageUrls ? StatamicImageNode::class : CustomImageNode::class;
        $customLinkMark = $this->withStatamicImageUrls ? StatamicLinkMark::class : CustomLinkMark::class;

        $renderer = (new Renderer)
            ->replaceNode(DefaultImageNode::class, $customImageNode)
            ->replaceMark(DefaultLinkMark::class, $customLinkMark)
            ->addNode(SetNode::class)
            ->addNodes(static::$customNodes)
            ->addMarks(static::$customMarks);

        foreach (static::$replaceNodes as $searchNode => $replaceNode) {
            $renderer->replaceNode($searchNode, $replaceNode);
        }

        foreach (static::$replaceMarks as $searchMark => $replaceMark) {
            $renderer->replaceMark($searchMark, $replaceMark);
        }

        return $renderer->render(['type' => 'doc', 'content' => $value]);
    }

    public static function addNode($node)
    {
        static::$customNodes[] = $node;
    }

    public static function addMark($mark)
    {
        static::$customMarks[] = $mark;
    }

    public static function replaceNode($searchNode, $replaceNode)
    {
        static::$replaceNodes[$searchNode] = $replaceNode;
    }

    public static function replaceMark($searchMark, $replaceMark)
    {
        static::$replaceMarks[$searchMark] = $replaceMark;
    }

    protected function convertToSets($html)
    {
        $arr = preg_split('/(<set>index-\d+<\/set>)/', $html, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        return collect($arr)->map(function ($html) {
            if (preg_match('/^<set>index-(\d+)<\/set>/', $html, $matches)) {
                return $this->sets[$matches[1]];
            }

            return ['type' => 'text', 'text' => $this->textValue($html)];
        });
    }

    protected function textValue($value)
    {
        $fieldtype = (new Text)->setField(new Field('text', [
            'antlers' => $this->fieldtype->config('antlers'),
        ]));

        return new Value($value, 'text', $fieldtype);
    }

    protected function augmentSets($value, $shallow)
    {
        $augmentMethod = $shallow ? 'shallowAugment' : 'augment';

        return $value->map(function ($set) use ($augmentMethod) {
            if (! $this->fieldtype->config("sets.{$set['type']}.fields")) {
                return $set;
            }

            $values = $this->fieldtype->fields($set['type'])->addValues($set)->{$augmentMethod}()->values()->all();

            return array_merge($values, ['type' => $set['type']]);
        })->all();
    }
}
