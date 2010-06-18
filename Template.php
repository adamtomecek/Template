<?php
/**
 * Template.php
 * 
 * Třída pro generování šablon využívající funkce eval
 * Class for template generating using eval function
 * 
 * Licencováno pod New BSD licencí 
 * Licenced under the New BSD License
 * 
 * Inspirováno Latte filtrem Nette frameworku (www.nette.org/cs) 
 * Inspired by Nette framework's Latte filter (www.nette.org/en)
 *
 * @author Adam Tomeček
 * 
 */
class Template{
	private $file;
	private $layout;
	
	/*array for not declared variables*/
	public $vars = array();

	public function __construct($template = NULL, $layout = NULL){
		if(!empty($template)){
			$this->setFile($template);
		}
		if(!empty($layout)){
			$this->layout = $layout;
		}
		return $this;
	}

	/*get all not declared variables*/
	public function getVars(){
		return $this->vars;	
	}

	/*function for getting variables which are not declared in class*/
	public function &__get($key){
		if (array_key_exists($key, $this->vars)) {
			return $this->vars[$key];
		}
	}
	
	/*function for setting variables which are not declared in class*/
	public function __set($key, $val){
		$this->vars[$key] = $val;
	} 


	/*Inspired by Nette framework Latte filter*/
	private function compile($file){
		if(is_file($file)){
			$keys = array(
				'{if %%}' => '<?php if (\1): ?>',
				'{elseif %%}' => '<?php ; elseif (\1): ?>',
				'{for %%}' => '<?php for (\1): ?>',
				'{foreach %%}' => '<?php foreach (\1): ?>',
				'{while %%}' => '<?php while (\1): ?>',
				'{/if}' => '<?php endif; ?>',
				'{/for}' => '<?php endfor; ?>',
				'{/foreach}' => '<?php endforeach; ?>',
				'{/while}' => '<?php endwhile; ?>',
				'{else}' => '<?php ; else: ?>',
				'{continue}' => '<?php continue; ?>',
				'{break}' => '<?php break; ?>',
				'{$%% = %%}' => '<?php $\1 = \2; ?>',
				'{$%%++}' => '<?php $\1++; ?>',
				'{$%%--}' => '<?php $\1--; ?>',
				'{$%%}' => '<?php echo $\1; ?>',
				'{comment}' => '<?php /*',
				'{/comment}' => '*/ ?>',
				'{/*}' => '<?php /*',
				'{*/}' => '*/ ?>',
				);
			
			foreach ($keys as $key => $val) {
				$patterns[] = '#' . str_replace('%%', '(.+)',
					preg_quote($key, '#')) . '#U';
				$replace[] = $val;
			}
		
		/*replace our pseudo language in template with php code*/
		return preg_replace($patterns, $replace, file_get_contents($file));
		}else{
			throw new Exception("Missing template file '$file'.");
		}
	}

	/*set function for layout only*/
	public function setLayout($layout){
		$this->layout = $layout;
		return $this;
	}
	
	/*set function for main template only*/
	public function setFile($template){
		$this->file = $template;
		return $this;
	}
	
	/*set function for main template and layout*/
	public function setup($template, $layout){
		$this->setFile($template);
		$this->setLayout($layout);
		return $this;
	}

	/*render main template file*/
	private function renderContent(){
		if(!empty($this->file)){
			if(is_file($this->file)){
				//compile template into php code
				$template = $this->compile($this->file);
				//evaluate compiled code
				return $this->evaluate($template, $this->getVars());
			}else{
				throw new Exception("Missing main template file '".$this->file."'.");
			}
		}else{
			//missing main template file
			throw new Exception("Main template file wasn't set.");
		}
	}

	/*render template*/
	public function render(){
		//are we using layout?
		if(!empty($this->layout)){
			if(is_file($this->layout)){
				//compile whole layout
				$template = $this->compile($this->layout);
			}else{
				throw new Exception("Missing layout template file '".$this->layout."'.");
			}
		}else{
			//or compile only main template file
			$template = $this->compile($this->file);
		}
		//evaluate compiled code
		return $this->evaluate($template, $this->getVars());
	}
	
	/*extended base evaluate function*/
	private function evaluate($code, array $variables = NULL){
		//get variables from template so we can call them only $variable onstead of $this->variable
		if($variables != NULL){
			extract($variables);
		}
		return eval('?>' . $code);
	}
}