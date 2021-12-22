<?php

declare(strict_types = 1);

namespace jojoe77777\FormAPI;

use pocketmine\player\Player;
use function count;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;

class CustomForm extends Form {

    private array $labelMap = [];
    /** @var callable[] */
    private array $validator = [];

    /**
     * @param callable|null $callable
     */
    public function __construct(?callable $callable) {
        parent::__construct($callable);
        $this->data["type"] = "custom_form";
        $this->data["title"] = "";
        $this->data["content"] = [];
    }

    public function processData(&$data) : void {
        if(is_array($data)) {
            $new = [];
            foreach ($data as $i => $v) {
                $new[$this->labelMap[$i]] = $v;
            }
            $data = $new;
        }
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title) : void {
        $this->data["title"] = $title;
    }

    /**
     * @return string
     */
    public function getTitle() : string {
        return $this->data["title"];
    }

    /**
     * @param string $text
     * @param string|null $label
     */
    public function addLabel(string $text, ?string $label = null) : void {
        $this->addContent(["type" => "label", "text" => $text]);
        $this->labelMap[] = $label ?? count($this->labelMap);
    }

    /**
     * @param string $text
     * @param bool|null $default
     * @param string|null $label
     */
    public function addToggle(string $text, bool $default = null, ?string $label = null) : void {
        $content = ["type" => "toggle", "text" => $text];
        if($default !== null) {
            $content["default"] = $default;
        }
        $this->addContent($content);
        $cnt = count($this->labelMap);
        $this->labelMap[] = $label ?? $cnt;
        $this->validator[$cnt] = fn($data) => is_bool($data);
    }

    /**
     * @param string $text
     * @param int $min
     * @param int $max
     * @param int $step
     * @param int $default
     * @param string|null $label
     */
    public function addSlider(string $text, int $min, int $max, int $step = -1, int $default = -1, ?string $label = null) : void {
        $content = ["type" => "slider", "text" => $text, "min" => $min, "max" => $max];
        if($step !== -1) {
            $content["step"] = $step;
        }
        if($default !== -1) {
            $content["default"] = $default;
        }
        $this->addContent($content);
        $cnt = count($this->labelMap);
        $this->labelMap[] = $label ?? $cnt;
        $this->validator[$cnt] = fn($data) => (is_int($data) || is_float($data)) && $data >= $min && $data <= $max;
    }

    /**
     * @param string $text
     * @param array $steps
     * @param int $defaultIndex
     * @param string|null $label
     */
    public function addStepSlider(string $text, array $steps, int $defaultIndex = -1, ?string $label = null) : void {
        $content = ["type" => "step_slider", "text" => $text, "steps" => $steps];
        if($defaultIndex !== -1) {
            $content["default"] = $defaultIndex;
        }
        $this->addContent($content);
        $cnt = count($this->labelMap);
        $this->labelMap[] = $label ?? $cnt;
        $this->validator[$cnt] = fn($data) => is_int($data) && $data >= 0 && $data < count($steps);
    }

    /**
     * @param string $text
     * @param array $options
     * @param int|null $default
     * @param string|null $label
     */
    public function addDropdown(string $text, array $options, int $default = null, ?string $label = null) : void {
        $this->addContent(["type" => "dropdown", "text" => $text, "options" => $options, "default" => $default]);
        $cnt = count($this->labelMap);
        $this->labelMap[] = $label ?? $cnt;
        $this->validator[$cnt] = fn($data) => is_int($data) && $data >= 0 && $data < count($options);
    }

    /**
     * @param string $text
     * @param string $placeholder
     * @param string|null $default
     * @param string|null $label
     */
    public function addInput(string $text, string $placeholder = "", string $default = null, ?string $label = null) : void {
        $this->addContent(["type" => "input", "text" => $text, "placeholder" => $placeholder, "default" => $default]);
        $cnt = count($this->labelMap);
        $this->labelMap[] = $label ?? count($this->labelMap);
        $this->validator[$cnt] = fn($data) => is_string($data);
    }

    /**
     * @param array $content
     */
    private function addContent(array $content) : void {
        $this->data["content"][] = $content;
    }

    public function validate(Player $player, $data): bool{
        if(!is_array($data)) {
            return false;
        }
        $cnt = count($data);
        if($cnt !== count($this->labelMap)) {
            return false;
        }
        foreach($this->validator as $k => $validation) {
            if(!$validation($data[$k])) {
                return false;
            }
        }
        return true;
    }

}
