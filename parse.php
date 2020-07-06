<?php	
	/**
	*	Autor:	Kristián Liščinský(xlisci01)
	*	1.projekt do predmetu IPP
	*	5.3.2018
	*	
	*	**poznámka**
	*	Používam slovenské komentáre, ale anglické názvy funkcií a premenných
	**/

	/**
	*	Načítavanie zo štandardného vstupu	
	*/
	function get_line(){
	
		while(($line = fgets(STDIN)) !== false){
			return $line;
		}
	}
	
	/**
	*	Vypísanie chyby na štandardný chybový výstup
	*	a navrátenie príslušného chybového kódu
	*	@param $number návratová chybová hodnota	
	*/
	function err_print($number){
		switch($number){
			case 10:
				fwrite(STDERR, "Forbidden combinations of arguments\n");
				break;
			case 12:
				fwrite(STDERR, "Error trying to open file\n");
				break;
			case 21: 
				fwrite(STDERR, "Lexical or syntactic error\n");
				break;
		}
	exit($number);
	}

	/**
	*	Funkcia na odfiltorvanie komentárov
	*	@param $string, riadok zo štandardného vstupu
	*	@return $string, riadok zo štandardého vstupu bez komentárov
	*/
	function comment_filter($string){
		$result = "";
		for($i = 0; $i <= strlen($string); $i++){
			$character = substr($string, $i, 1);
			if($character == "#"){
				return $result;			
			}
			$result = $result.$character;		
		}
		return $result;
	}
	
	/**	
	*	Funckia na rozdelenie načítaného riadku na jednotlivé časti(tokeny)
	*	@param $string, riadok zo štandardného vstupu
	*	@return	$string, pole tokenov
	*/
	function whitespace_remove($string){
		$string = trim($string);
		$string = comment_filter($string);
		$string = preg_split('/\s+/', $string);
		return $string;
	}

	/**
	*	Funkcia na zistenie, či sa jedná o typ <label>
	*	@param $string, reťazec, u ktorého chceme zistiť, či je vyhovuje typu <label>
	*	@return $array, vracia pole v prípade úspechu	
	*/
	function is_label($string){
		//prvým znakom mǒže byť písmeno, alebo špeciálny znak !!Nie číslo	
		$result = "";
		$first = substr($string, 0, 1);
		
		if(!(preg_match('/[-_$&*%]/', $first) or preg_match('/[a-zA-Z]/', $first))){		
			return false;		
		}
		//prevedenie znaku &, kvôli zápisu do XML súboru
		if($first === "&"){
			$first = "&amp;";
		}
		$result = $result.$first;
		//ako ďalší znak môže byť aj číslo
		for($i = 1; $i < strlen($string); $i++){
			$character = substr($string, $i, 1);
			if(!(preg_match('/[-_$&*%]/', $character) or preg_match('/[a-zA-Z0-9]/', $character))){
				return false;
			}
			if($character === "&"){
			$character = "&amp;";
			}
		$result = $result.$character;
		}
		$array[] = "OK"; $array[] = $result;
		return $array;
	}

	/*
	*	Funkcia na zistenie, či sa jedná o typ <var>
	*	@param $string, reťazec, u ktorého chceme zistiť, či je vyhovuje typu <var>
	*	@return $array, vracia pole v prípade úspechu	
	*/
	function is_variable($string){
		if(substr_count($string, "@") !== 1){
			return false;		
		}
		
		$string = preg_split('/[@]/', $string);
		$get_label = "";
		if(is_label($string[1])){
			
			if(preg_match('/^LF$|^TF$|^GF$/', $string[0])){
				$get_label = is_label($string[1]);
				$array[] = "OK"; $array[] = $string[0]; $array[] = $get_label[1];
				return $array;
			}
			else{
				return false;		
			}
		}
		else{
			return false;		
		}
	}

	/*
	*	Funkcia na zistenie, či sa jedná o validný zápis číslo typu <int>
	*	@param $string, reťazec, u ktorého chceme zistiť, či je vyhovuje typu <int>
	*	@return $result, vracia dané číslo v prípade úspechu	
	*/
	function integer_control($string){
		$result = "";
		$character = substr($string, 0, 1);
		$result = $result.$character;

		//môže začínať znakom -
		if($character == "-"){
			if((strlen($string) > 1)){
				for($i = 1; $i < strlen($string); $i++){
				$character = substr($string, $i, 1);
				$result = $result.$character;
					if(!(preg_match('/[0-9]/',$character))){
						return false;				
					}
				}
				return $result;
			}
			else{
				return false;
			}
		}
		//môže začínať znakom +
		else if($character == "+"){
			if((strlen($string) > 1)){
				for($i = 1; $i < strlen($string); $i++){
				$character = substr($string, $i, 1);
				$result = $result.$character;
					if(!(preg_match('/[0-9]/',$character))){
						return false;				
					}
				}
				return $result;
			}
			else{
				return false;
			}
		}
		//znaky + a - sa nemusia nachádzať pred číslo
		else if(preg_match('/[0-9]/',$character)){
			for($i = 1; $i < strlen($string); $i++){
				$character = substr($string, $i, 1);
				$result = $result.$character;

				if(!(preg_match('/[0-9]/',$character))){
					return false;				
				}
			}
			return $result;
		}
		else{
			return false;
		}
	}

	/**
	*	Funkcia na prevod & do podoby, v ktorej ho možno zapísať
	*	do súboru xml
	*	@param $string, reťazec, ktorý môže obsahovať &
	*	@return $result, reťazec s prevedeným znakom & podľa špecifikácie, teda &amp;	
	*/
	function taboo_characters($string){
		
		$result = "";
		for($i = 0; $i < strlen($string); $i++){
			$character = substr($string, $i, 1);
			if($character === "&"){
				$character = "&amp;";
			}
			$result = $result.$character;
		}
	return $result;
	}

	/**
	*	Funkcia na zistenie, či sa jedná o typ <string>
	*	@param $string, reťazec, ktorý má otestovať
	*	@return $string, reťazec s prevedeným znakom & podľa špecifikácie, teda &amp;	
	*/
	function string_control($string){
		
		$vysledok = "";
		for($i = 0; $i < strlen($string); $i++){
			$character = substr($string, $i, 1);
		
			//bez escape sekvencie
			if( $character !== "\\" ){
				$vysledok = $vysledok.$character;
			}
			//prípad overenia escape sekvencie (očakávame 3 čísla, inak chyba)
			else if($character == "\x5C"){
				$escape = "";
				$i++;
				$character = substr($string, $i, 1);
				if(preg_match('/[0-9]/',$character)){
					$escape = $escape.$character;

					$i++;
					$character = substr($string, $i, 1);
					if(preg_match('/[0-9]/',$character)){
						$escape = $escape.$character;
						
						$i++;
						$character = substr($string, $i, 1);
						if(preg_match('/[0-9]/',$character)){
							$escape = $escape.$character;
							$konecna = (int)$escape;
							$konecna = chr($konecna);
							$vysledok = $vysledok.$konecna;
						}
						else{
							return false;		
						}	
					}
					else{
						return false;		
					}
				}
				else{
					return false;		
				}

			}
			else{
				return false;
			}
		}
	//v prípade výskytu & v reťazci, ho prevedieme na &amp;
	return taboo_characters($string);
	}

	
	/**
	*	Funkcia na zistenie, či sa jedná o typ <symb>
	*	@param $string, reťazec, ktorý má otestovať
	*	@return $array, v prípade úspechu vracia 3-rozmerne pole	
	*/
	function is_symbol($string){
		$string = preg_split('/[@]/', $string);
		$counter = count($string);
		if($counter > 2){
			for($i = 2; $i < $counter; $i++){
				$string[1] = $string[1]."@".$string[$i];
			}			
		}
		//prvá časť reťazca
		if(preg_match('/^bool$|^int$|^string$/', $string[0])){
			//konštanty pre typ bool
			if($string[0] == "bool"){
				if($string[1] == "true"){
					$array[] = "OK"; $array[] = "bool"; $array[] = "true";
					return $array;
				}
				else if($string[1] == "false"){
					$array[] = "OK"; $array[] = "bool"; $array[] = "false";
					return $array;
				}
				else{
					return false;
				}
			}
			//v prípade čísla
			else if($string[0] == "int"){
				//ak je číslo 0, podmienta padne
				$zero = integer_control($string[1]);
				if($zero === "0"){
					$vysledok = integer_control($string[1]);
					$array[] = "OK"; $array[] = "int"; $array[] = $vysledok;
					return $array;
				}

				if(integer_control($string[1])){
					$vysledok = integer_control($string[1]);
					$array[] = "OK"; $array[] = "int"; $array[] = $vysledok;
					return $array;
				}
				else{
					return false;				
				}
			}
			//ak sa jedna o typ <string>
			else if($string[0] == "string"){
				if($string[1] == ""){
					$array[] = "OK"; $array[] = "string"; $array[] = "";
					return $array;	
				}
				else{
					if(string_control($string[1])){
						$vysledok = string_control($string[1]);
						$array[] = "OK"; $array[] = "string"; $array[] = $vysledok;
						return $array;
					}			
					else{
						return false;
					}	
				}
			}
			else{
				return false;	
			}
		}
		else{
			return false;
		}
		
	}
	
	/**
	*	Funkcia na overenie, či za sebou tokeny nasledujú v poradí
	*	<var><symb1><symb2>
	*	@param $string, pole reťazcov s tokenmi
	*	@param $opcode, počítadlo inštrukcií
	*	@param $xml, xml, do ktorého zapisujeme
	*	@param $iterátor 
	*/
	function var_symbol_symbol($string, $opcode, $xml, $position){
		
		//počet tokenov musí byť 4 (inštrukcia, <var>, <symb1>, <symb2>)		
		if(count($string) == 4){
					;
		}
		//v prípade ak nasleduje komentár
		else if(count($string) == 5){
			if($string[4] !== ""){
				err_print(21);
			}
		}
		else{
			err_print(21);
		}
	
		//zápis do xml
		$instruction = $xml->addChild('instruction');
		$instruction->addAttribute("order", $position);
		$instruction->addAttribute("opcode", $opcode);
		$position++;
		$get_variable = "";
		//nasleduje <var>
		if(is_variable($string[1])){
			$get_variable = is_variable($string[1]);
			$var = $instruction->addChild('arg1', $get_variable[1]."@".$get_variable[2]);
			$var->addAttribute("type", "var");
			//nasleduje <symb>
			if(is_symbol($string[2]) or is_variable($string[2])){
				//nasleduje <symb>
				if(is_symbol($string[2])){
					$type = is_symbol($string[2]);
					$var = $instruction->addChild('arg2', $type[2]);
					$var->addAttribute("type", $type[1]);
				}
				$get_variable = "";
				if(is_variable($string[2])){
					$get_variable = is_variable($string[2]);
					$var = $instruction->addChild('arg2', $get_variable[1]."@".$get_variable[2]);
					$var->addAttribute("type", "var");
				}
				//nasleduje <symb>
				if(is_symbol($string[3]) or is_variable($string[3])){
					if(is_symbol($string[3])){
						$type = is_symbol($string[3]);
						$var = $instruction->addChild('arg3', $type[2]);
						$var->addAttribute("type", $type[1]);
					}
					$get_variable = "";
					if(is_variable($string[3])){
						$get_variable = is_variable($string[3]);
						$var = $instruction->addChild('arg3', $get_variable[1]."@".$get_variable[2]);
						$var->addAttribute("type", "var");
					}
					return $position;
				}
				else{
					err_print(21);
				}
			}
			else{
				err_print(21);
			}
		}
		else{
			err_print(21);
		}
	}

	/**
	*	Funkcia na overenie, či za sebou tokeny nasledujú v poradí
	*	inštrukcia bez parametrov
	*	@param $string, pole reťazcov s tokenmi
	*	@param $opcode, počítadlo inštrukcií
	*	@param $xml, xml, do ktorého zapisujeme
	*	@param $iterátor
	*	@return $position, počítadlo inštrukcií
	*/
	function without_arguments($string, $opcode, $xml, $position){
		//očákavame inštrukciu bez argumentov		
		if(count($string) == 1){
			;
		}
		else if(count($string) == 2){
			if($string[1] !== ""){
				err_print(21);
			}
		}
		else{
			err_print(21);
		}
		//zápis do xml
		$instruction = $xml->addChild('instruction');
		$instruction->addAttribute("order", $position);
		$instruction->addAttribute("opcode", $opcode);
		$position++;

		return $position;
	}
	
	/**
	*	Funkcia na overenie, či za sebou tokeny nasledujú v poradí
	*	inštrukcia <label>
	*	@param $string, pole reťazcov s tokenmi
	*	@param $opcode, počítadlo inštrukcií
	*	@param $xml, xml, do ktorého zapisujeme
	*	@param $iterátor
	*	@return $position, počítadlo inštrukcií
	*/
	function only_label($string, $opcode, $xml, $position){
		//inštrukcia a jej parameter
		if(count($string) == 2){
				;
		}
		//prípadný komentár
		else if(count($string) == 3){
			if($string[2] !== ""){
				err_print(21);
			}
		}
		else{
			err_print(21);
		}
			
			$instruction = $xml->addChild('instruction');
			$instruction->addAttribute("order", $position);
			$instruction->addAttribute("opcode", $opcode);
			$position++;
			$get_label = "";
			//nasleduje <label>
			if(is_label($string[1])){
				$get_label = is_label($string[1]);
				$label = $instruction->addChild('arg1', $get_label[1]);
				$label->addAttribute("type", "label");
				return $position;
			}
			else{
				err_print(21);
			}
	}
	
	/**
	*	Funkcia na overenie, či za sebou tokeny nasledujú v poradí
	*	inštrukcia <symb>
	*	@param $string, pole reťazcov s tokenmi
	*	@param $opcode, počítadlo inštrukcií
	*	@param $xml, xml, do ktorého zapisujeme
	*	@param $iterátor
	*	@return $position, počítadlo inštrukcií
	*/
	function only_symbol($string, $opcode, $xml, $position){
		//inštrukcia a jej jeden parameter
		if(count($string) == 2){
			;
		}
		//prípadný komentár
		else if(count($string) == 3){
			if($string[2] !== ""){
				err_print(21);
			}
		}
		else{
			err_print(21);
		}
			//zápis do xml	
			$instruction = $xml->addChild('instruction');
			$instruction->addAttribute("order", $position);
			$instruction->addAttribute("opcode", $opcode);
			$position++;
			//nasleduje <symb>
			$get_variable = "";
			if(is_variable($string[1]) or is_symbol($string[1])){
				if(is_symbol($string[1])){
					$type = is_symbol($string[1]);
					$var = $instruction->addChild('arg1', $type[2]);
					$var->addAttribute("type", $type[1]);
				}
				
				if(is_variable($string[1])){
					$get_variable = is_variable($string[1]);
					$var = $instruction->addChild('arg1', $get_variable[1]."@".$get_variable[2]);
					$var->addAttribute("type", "var");
				}
			
				return $position;
			}
			else{
				err_print(21);
			}
	}
	
	/**
	*	Funkcia na overenie, či za sebou tokeny nasledujú v poradí
	*	<label><symb1><symb2>
	*	@param $string, pole reťazcov s tokenmi
	*	@param $opcode, počítadlo inštrukcií
	*	@param $xml, xml, do ktorého zapisujeme
	*	@param $iterátor 
	*/
	function label_symb_symb($string, $opcode, $xml, $position){
		//inštrukcia a jej 3 argumenty		
		if(count($string) == 4){
				;
		}
		//prípadný komentár
		else if(count($string) == 5){
			if($string[4] !== ""){
				err_print(21);
			}
		}
		else{
			err_print(21);
		}
			//zápis do xml
			$instruction = $xml->addChild('instruction');
			$instruction->addAttribute("order", $position);
			$instruction->addAttribute("opcode", $opcode);
			$position++;
			$get_label = "";
			//nasleduje <label>
			if(is_label($string[1])){
				$get_label = is_label($string[1]);
				$label = $instruction->addChild('arg1', $get_label[1]);
				$label->addAttribute("type", "label");
				
				//nasleduje <symb>
				if(is_symbol($string[2]) or is_variable($string[2])){
					if(is_symbol($string[2])){
					$type = is_symbol($string[2]);
					$var = $instruction->addChild('arg2', $type[2]);
					$var->addAttribute("type", $type[1]);
				}
				$get_variable = "";
				if(is_variable($string[2])){
					$get_variable = is_variable($string[2]);
					$var = $instruction->addChild('arg2', $get_variable[1]."@".$get_variable[2]);
					$var->addAttribute("type", "var");
				}
					//nasleduje <symb>
					if(is_symbol($string[3]) or is_variable($string[3])){
						if(is_symbol($string[3])){
							$type = is_symbol($string[3]);
							$var = $instruction->addChild('arg3', $type[2]);
							$var->addAttribute("type", $type[1]);
						}
						$get_variable = "";
						if(is_variable($string[3])){
							$get_variable = is_variable($string[3]);
							$var = $instruction->addChild('arg3', $get_variable[1]."@".$get_variable[2]);
							$var->addAttribute("type", "var");
						}
					
						return $position;
					}
					else{
						err_print(21);
					}
				}
				else{
					err_print(21);
				}
			}
			else{
				err_print(21);
			}
	}

	/**
	*	Funkcia na overenie, či za sebou tokeny nasledujú v poradí
	*	<var><symb>
	*	@param $string, pole reťazcov s tokenmi
	*	@param $opcode, počítadlo inštrukcií
	*	@param $xml, xml, do ktorého zapisujeme
	*	@param $iterátor 
	*/
	function var_symbol($string, $opcode, $xml, $position){
		//inštrukcia a jej 2 argumenty		
		if(count($string) == 3){
			;
		}
		//prípadný komentár
		else if(count($string) == 4){
			if($string[3] !== ""){
				err_print(21);
			}
		}
		else{
			err_print(21);
		}
	
		//zápis do xml
		$instruction = $xml->addChild('instruction');
		$instruction->addAttribute("order", $position);
		$instruction->addAttribute("opcode", $opcode);
		$position++;
		$get_variable = "";
		//nasleduje <var>
		if(is_variable($string[1])){
			
			$get_variable = is_variable($string[1]);
			$var = $instruction->addChild('arg1', $get_variable[1]."@".$get_variable[2]);
			$var->addAttribute("type", "var");
			//nasleduje <symb>
			if(is_symbol($string[2]) or is_variable($string[2])){
				//nasleduje <symb>
				if(is_symbol($string[2])){
					$type = is_symbol($string[2]);
					$var = $instruction->addChild('arg2', $type[2]);
					$var->addAttribute("type", $type[1]);
				}
				$get_variable = "";
				if(is_variable($string[2])){
					$get_variable = is_variable($string[2]);
					$var = $instruction->addChild('arg2', $get_variable[1]."@".$get_variable[2]);
					$var->addAttribute("type", "var");
				}
				return $position;
			}
			else{
				err_print(21);
			}
		}
		else{
			err_print(21);
		}
	}
	
	/**
	*	Funkcia na overenie, či za sebou tokeny nasledujú v poradí
	*	inštrukcia <var>
	*	@param $string, pole reťazcov s tokenmi
	*	@param $opcode, počítadlo inštrukcií
	*	@param $xml, xml, do ktorého zapisujeme
	*	@param $iterátor
	*	@return $position, počítadlo inštrukcií
	*/
	function only_variable($string, $opcode, $xml, $position){
		//inštrukcia a jej jeden argument <var>		
		if(count($string) == 2){
				;
		}
		//v prípade, ak za nimi nasleduje komentár
		else if(count($string) == 3){
			if($string[2] !== ""){
				err_print(21);
			}
		}
		else{
			err_print(21);
		}
		
		//zápis do xml
		$instruction = $xml->addChild('instruction');
		$instruction->addAttribute("order", $position);
		$instruction->addAttribute("opcode", $opcode);
		$position++;
		$get_variable = "";
		//nasleduje <var>
		if(is_variable($string[1])){
			$get_variable = is_variable($string[1]);
			$var = $instruction->addChild('arg1', $get_variable[1]."@".$get_variable[2]);
			$var->addAttribute("type", "var");
			return $position;
		}
		else{
			err_print(21);
		}
		
	}

	/**
	*	Funkcia na overenie, či za sebou tokeny nasledujú v poradí
	*	inštrukcia <type><symb>
	*	@param $string, pole reťazcov s tokenmi
	*	@param $opcode, počítadlo inštrukcií
	*	@param $xml, xml, do ktorého zapisujeme
	*	@param $iterátor
	*	@return $position, počítadlo inštrukcií
	*/
	function var_type($string, $opcode, $xml, $position){
		//inštrukcia a jej 2 argumenty <var><type>		
		if(count($string) == 3){
			;
		}
		//prípadný komentár
		else if(count($string) == 4){
			if($string[3] !== ""){
				err_print(21);
			}
		}
		else{
			err_print(21);
		}
		
		//zápis do xml
		$instruction = $xml->addChild('instruction');
		$instruction->addAttribute("order", $position);
		$instruction->addAttribute("opcode", $opcode);
		$position++;
		$get_variable = "";
		//nasleduje <var>
		if(is_variable($string[1])){
			$get_variable = is_variable($string[1]);
			$var = $instruction->addChild('arg1', $get_variable[1]."@".$get_variable[2]);
			$var->addAttribute("type", "var");
			//nasleduje <type>
			if(!strcmp($string[2], "int")){
				$var2 = $instruction->addChild('arg2', $string[2]);
				$var2->addAttribute("type", "type");
				return $position;
			}
			else if(!strcmp($string[2], "string")){
				$var2 = $instruction->addChild('arg2', $string[2]);
				$var2->addAttribute("type", "type");
				return $position;
			}
			else if(!strcmp($string[2], "bool")){
				$var2 = $instruction->addChild('arg2', $string[2]);
				$var2->addAttribute("type", "type");
				return $position;
			}
			else{
				err_print(21);
			}
		}
		else{
			err_print(21);
		}
	}
	
	/**
	*	Kontrola postupnosti tokenov
	*	@param	$string, pole reťazcov, celý riadok zo vstupu
	*	@param	$xml, xml do ktorého zapisujeme 
	*	@param	$position, počítadlo inštrukcií
	*	$return $position, počítadlo inštrukcií
	*/
	function check_if_instruction($string, $xml, $position){
	
	//počet tokenov v jednom riadku nemôže byť vačsí ako 5
	if((count($string)) > 5){
		err_print(21);
	}

		//inštrukcia MOVE
		if (!(strcasecmp($string[0], "move"))){		
			$position = var_symbol($string, "MOVE", $xml, $position);
			return $position;
		}
		//inštrukcia CREATEFRAME
		else if (!(strcasecmp($string[0], "createframe"))){			
			$position = without_arguments($string, "CREATEFRAME", $xml, $position);
			return $position;
		}
		//inštrukcia PUSHFRAME
		else if (!(strcasecmp($string[0], "pushframe"))){
			$position = without_arguments($string, "PUSHFRAME", $xml, $position);
			return $position;
		}
		//inštrukcia POPFRAME
		else if (!(strcasecmp($string[0], "popframe"))){
			$position = without_arguments($string, "POPFRAME", $xml, $position);
			return $position;
		}
		//inštrukcia DEFVAR
		else if (!(strcasecmp($string[0], "defvar"))){
			$position = only_variable($string, "DEFVAR", $xml, $position);
			return $position;			
		}
		//inštrukcia CALL
		else if (!(strcasecmp($string[0], "call"))){
			$position = only_label($string, "CALL", $xml, $position);
			return $position;
		}
		//inštrukcia RETURN
		else if (!(strcasecmp($string[0], "return"))){
			$position = without_arguments($string, "RETURN", $xml, $position);
			return $position;
		}
		//inštrukcia PUSHS
		else if (!(strcasecmp($string[0], "pushs"))){
			$position = only_symbol($string, "PUSHS", $xml, $position);
			return $position;
		}
		//inštrukcia POPS
		else if (!(strcasecmp($string[0], "pops"))){
			$position = only_variable($string, "POPS", $xml, $position);
			return $position;
		}
		//inštrukcia ADD
		else if (!(strcasecmp($string[0], "add"))){
			$position = var_symbol_symbol($string, "ADD", $xml, $position);
			return $position;
		}
		//inštrukcia SUB
		else if (!(strcasecmp($string[0], "sub"))){
			$position = var_symbol_symbol($string, "SUB", $xml, $position);
			return $position;
		}
		//inštrukcia MUL
		else if (!(strcasecmp($string[0], "mul"))){
			$position = var_symbol_symbol($string, "MUL", $xml, $position);
			return $position;
		}
		//inštrukcia IDIV
		else if (!(strcasecmp($string[0], "idiv"))){
			$position = var_symbol_symbol($string, "IDIV", $xml, $position);
			return $position;
		}
		//inštrukcia LT
		else if (!(strcasecmp($string[0], "lt"))){
			$position = var_symbol_symbol($string, "LT", $xml, $position);
			return $position;
		}
		//inštrukcia GT
		else if (!(strcasecmp($string[0], "gt"))){
			$position = var_symbol_symbol($string, "GT", $xml, $position);
			return $position;
		}
		//inštrukcia EQ
		else if (!(strcasecmp($string[0], "eq"))){
			$position = var_symbol_symbol($string, "EQ", $xml, $position);
			return $position;
		}
		//inštrukcia AND
		else if (!(strcasecmp($string[0], "and"))){
			$position = var_symbol_symbol($string, "AND", $xml, $position);
			return $position;
		}
		//inštrukcia OR
		else if (!(strcasecmp($string[0], "or"))){
			$position = var_symbol_symbol($string, "OR", $xml, $position);
			return $position;
		}
		//inštrukcia NOT
		else if (!(strcasecmp($string[0], "not"))){
			$position = var_symbol($string, "NOT", $xml, $position);
			return $position;
		}
		//inštrukcia INT2CHAR
		else if (!(strcasecmp($string[0], "int2char"))){
			$position = var_symbol($string, "INT2CHAR", $xml, $position);
			return $position;
		}
		//inštrukcia STRI2INT
		else if (!(strcasecmp($string[0], "stri2int"))){
			$position = var_symbol_symbol($string, "STRI2INT", $xml, $position);
			return $position;
		}
		//inštrukcia READ
		else if (!(strcasecmp($string[0], "read"))){
			$position = var_type($string, "READ", $xml, $position);
			return $position;
		}
		//inštrukcia WRITE
		else if (!(strcasecmp($string[0], "write"))){
			$position = only_symbol($string, "WRITE", $xml, $position);
			return $position;
		}
		//inštrukcia CONCAT
		else if (!(strcasecmp($string[0], "concat"))){
			$position = var_symbol_symbol($string, "CONCAT", $xml, $position);
			return $position;
		}
		//inštrukcia STRLEN
		else if (!(strcasecmp($string[0], "strlen"))){
			$position = var_symbol($string, "STRLEN", $xml, $position);
			return $position;
		}
		//inštrukcia GETCHAR
		else if (!(strcasecmp($string[0], "getchar"))){
			$position = var_symbol_symbol($string, "GETCHAR", $xml, $position);
			return $position;
		}
		//inštrukcia SETCHAR
		else if (!(strcasecmp($string[0], "setchar"))){
			$position = var_symbol_symbol($string, "SETCHAR", $xml, $position);
			return $position;
		}
		//inštrukcia TYPE
		else if (!(strcasecmp($string[0], "type"))){
			$position = var_symbol($string, "TYPE", $xml, $position);
			return $position;
		}
		//inštrukcia LABEL
		else if (!(strcasecmp($string[0], "label"))){
			$position = only_label($string, "LABEL", $xml, $position);
			return $position;
		}
		//inštrukcia JUMP
		else if (!(strcasecmp($string[0], "jump"))){
			$position = only_label($string, "JUMP", $xml, $position);
			return $position;
		}
		//inštrukcia JUMPIFEQ
		else if (!(strcasecmp($string[0], "jumpifeq"))){
			$position = label_symb_symb($string, "JUMPIFEQ", $xml, $position);
			return $position;
		}
		//inštrukcia JUMPIFNEQ
		else if (!(strcasecmp($string[0], "jumpifneq"))){
			$position = label_symb_symb($string, "JUMPIFNEQ", $xml, $position);
			return $position;
		}
		//inštrukcia DPRINT
		else if (!(strcasecmp($string[0], "dprint"))){
			$position = only_symbol($string, "DPRINT", $xml, $position);
			return $position;	
		}
		//inštrukcia BREAK
		else if (!(strcasecmp($string[0], "break"))){
			$position = without_arguments($string, "BREAK", $xml, $position);
			return $position;
		}
		//prázdny riadok
		else if(!(strcasecmp($string[0], ""))){
			return $position;	
		}
		//inak sa jedná o syntaktickú chybu
		else{
			err_print(21);
		}
	}
	
	/**
	*	Funkcia na kontrolu, či program začína zápisom .IPPcode18
	*	pričom nezáleží na veľkosti písmen
	*	implementácia počíta s tým, že zápisu .IPPcode18
	*	môžu predchádzať prázdne riakdky, alebo komentáre
	*/
	function valid_ipp18(){
		//preskakovanie práznych znakov
		while($begin = get_line()){
			$begin = whitespace_remove($begin);
			if(count($begin) == 1){
				if(!strcasecmp($begin[0],".IPPcode18")){
					return true;		
				}
				else if($begin[0] === ""){
					;
				}
				else{
					err_print(21);
				}
			}
			else if(count($begin) == 2){
				if(!strcasecmp($begin[0],".IPPcode18") and $begin[1] === ""){
					return true;
				}
				else{
					err_print(21);
				}
			}
			else{
				err_print(21);
			}
		}
	}

	/**
	*	Funkcia na vypísanie nápovedy na štandardný výstup
	*	program v tomto prípade nenačitáva žiaden vstup
	*	**Poznámka
	*	výstup v češtine
	*/
	function print_help(){	
	echo"Skript typu filtr(parse.php v jazyce PHP 5.6) načte ze standardního vstupu zdrojový kód
v IPPcode18, zkontroluje lexikální a syynktatickou správnost kódu a vypíše na standardní
výstup XML reprezentaci programu dle specifikaci. Skript podporuje sbíraní statistik 
zpracovavaného kódu v IPPcode18.

Tento skript pracuje s těmito parametry:
	--help		vypíše na standardní výstup nápovědu (nenačíta žádny vstup)

	--stats=file 	pro zadaní souboru file, kam se agregované statistiky budou vypisovat
			po řádcích dle pořadí v ďalších parametrech:
				--loc		vypíše do statistik počet řádku s instrukcemi,
						nepočítají se prázdné řádky ani řádky obsahujíci
						pouze komentář, ani úvodný řádek
				--comments	vypíše do statistik počet řádku, na kterých
						se vyskytoval komentář\n";		
		exit(0);
	}

	/**
	*	Funkcia na generovanie XML formátu
	*	@return $result, vracia počet inštrukcií a počet komentárov	
	*/
	function generate_xml(){
		//kontorlujeme, či program začína zápisom .IPPcode18
		if(valid_ipp18()){
			;	
		}
		
		//xml pomocou metódy SimpleXMLElement
		$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><program/>');
		$xml->addAttribute("language", "IPPcode18");
		$position = 1;
		$comments = 0;
		
		//postupujeme všetkými riadkami
		while($pomoc = get_line()){
			/*
				počítadlo komentárov pre rozšírenie STATP
				komentáre pred zápisom .IPPcode18 nezapočitávame
			*/
			for($i = 0; $i <= strlen($pomoc); $i++){
				$character = substr($pomoc, $i, 1);
				if($character == "#"){
					$comments++;
					break;			
				}		
			}

			$pomoc = whitespace_remove($pomoc);
			$position = check_if_instruction($pomoc, $xml, $position);
		}

		//vypísanie XML na štandardný výstup
		$dom = new DOMDocument("1.0", "utf-8");
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;
		$dom->loadXML($xml->asXML());
		echo $dom->saveXML();
		$result[] = --$position;
		$result[] = $comments;

		return $result;
	}
	
	/**
	*	Spracovanie argumentov programu
	*/
	//bez argumentov
	if($argc == 1){
		generate_xml();
		return 0;
	}

	//jeden argument, --help alebo --stats
	else if($argc == 2){
		if($argv[1] === "--help"){
			print_help();
		}
		else if(preg_match('/^--stats=.*$/',$argv[1])){
			$string = preg_split('/^--stats=/', $argv[1]);			
			if(preg_match('/^--stats=/', $argv[1])){
				if($string[1]===""){
					err_print(12);//or 12
				}
				else{
					generate_xml();//not generate statics according IPP forum, only xml
					return 0;	
				}
			}
			else{
				err_print(10);
			}
		}
		else{
			err_print(10);
		}
	}
	//dva argumenty, --stats s jedným prepínačom
	else if($argc == 3){
		$string = preg_split('/^--stats=/', $argv[1]);			
			if(preg_match('/^--stats=/', $argv[1])){
				if($string[1]==""){
					err_print(12);//or 12
				}
				else{
					$filename = $string[1];
					if($argv[2] == "--loc"){
						$loc = generate_xml();
						file_put_contents($filename, "$loc[0]\n");
						return 0;
					}
					else if($argv[2] == "--comments"){
						$comments = generate_xml();
						file_put_contents($filename, "$comments[1]\n");
						return 0;
					}
					else{
						err_print(10);
					}		
				}
			}
			else{
				err_print(10);
			}
	}
	//3 argumenty, --stats s oboma prepínačmi
	else if($argc == 4){
		$string = preg_split('/^--stats=/', $argv[1]);			
			if(preg_match('/^--stats=/', $argv[1])){
				if($string[1]===""){
					err_print(10);//or 12
				}
				else{
					$filename = $string[1];
					if($argv[2] == "--loc"){
						if($argv[3] == "--comments"){
							$comments = generate_xml();
							file_put_contents($filename, "$comments[0]\n$comments[1]\n");
							return 0;
						}
						else{
							err_print(10);
						}
					}
					else if($argv[2] == "--comments"){
						if($argv[3] == "--loc"){
							$comments = generate_xml();
							file_put_contents($filename, "$comments[1]\n$comments[0]\n");
							return 0;
						}
						else{
							err_print(10);
						}
					}
					else{
						err_print(10);
					}		
				}
			}
			else{
				err_print(10);
			}
	}
	//viac ako 3 argumenty program obsahovať nemôže
	else{
		err_print(10);
	}

?>
