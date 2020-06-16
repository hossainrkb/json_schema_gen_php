<?php
class JsonSchemaGen {
    public function JSONbuild($jsonData) {
        $keys = array_keys(get_object_vars($jsonData));

        $op = new stdClass;
        $op->required = $keys;
        $op->properties = new stdClass;

        foreach ($keys as $eachKey => $eachValue) {
            $value = $jsonData->$eachValue;

            $dataType = gettype($value);

            if (!in_array($dataType, ["array", "object", "null"])) {
                $typeObj = new stdClass;
                $typeObj->type = $dataType;
                $op->properties->$eachValue = $typeObj;
            } else {
                switch ($dataType) {
                case 'array':
                    $dataType = gettype($jsonData->$eachValue[0]);
                    if ($dataType == "array") {
                        throw new Exception("complex array haven't handled yet", 1);
                    }
                    if ($dataType == "object") {
                        $NewtypeObj = new stdClass;
                        $NewtypeObj->type = "array";
                        $item = new stdClass;
                        $item->type = $dataType;
                        $item->properties = $this->JSONbuild($jsonData->$eachValue[0]);
                        $NewtypeObj->item = $item;
                        $op->properties->$eachValue = $NewtypeObj;
                        break;
                    }
                    $NewtypeObj = new stdClass;
                    $NewtypeObj->type = "array";
                    $item = new stdClass;
                    $item->type = $dataType;
                    $NewtypeObj->item = $item;
                    $op->properties->$eachValue = $NewtypeObj;
                    break;
                case 'object':
                    $NewtypeObj = new stdClass;
                    $NewtypeObj->type = "object";
                    $NewtypeObj->properties = $this->JSONbuild($jsonData->$eachValue);
                    $op->properties->$eachValue = $NewtypeObj;
                    break;
                default:
                    # code...
                    break;
                }
            }
        }
        return ($op);
    }

}
try {
    $json_data = json_decode(file_get_contents('input.json'));
    if (gettype($json_data) != "object") {
        throw new Exception("Not supported Type", 1);
    } else {
        $obj = new JsonSchemaGen();
        $vvv = $obj->JSONbuild($json_data);
        echo json_encode($vvv);
    }
} catch (\Exception $th) {
    echo $th->getMessage();
}
