<?php

/**
 * Basic replacement for {@link SplEnum}, an useful class that got outdated and needs a patched extension to work.
 * Still brings some security against bad input to the code and is useful to show some PHP capabilities.
 * Missing some extra features, like default value, strict checks...
 */
abstract class Enum {

    private static array $constCache = [];
    protected $value;

    public function getConstList() {
        if (!isset(self::$constCache[static::class])) {
            self::$constCache[static::class] = (new ReflectionClass(static::class))->getConstants();
        }
        return self::$constCache[static::class];
    }

    public function __construct($initial_value) {
        if (in_array($initial_value, $this->getConstList())) {
            $this->value = $initial_value;
        } else {
            throw new UnexpectedValueException("Invalid value '$initial_value' for Enum ".static::class.'; use one of the available constants in that class.');
        }
    }

    public function __toString() {
        return (string)$this->value;
    }

}

class EamsGovParser extends Enum {

    const CARRIERS = 'carrier';
    const REPS     = 'rep';

    function txt(): string {
        switch ((string)$this) {
            case self::REPS:        return 'EAMSReps';
            case self::CARRIERS:    return 'EAMSClaimsAdmins';
            default:                throw new UnexpectedValueException();
        }
    }

    /**
     * @param string $type One of this class' constants
     */
    public static function run(string $type) {
        $txt   = (new self($type))->txt(); //will fail if the string given is invalid
        $table = "cse_eams_{$type}s";
        $key   = "{$type}_id";

        set_time_limit(5 * MIN);

        $rows = explode("\r\n", file_get_contents("http://www.dir.ca.gov/ftproot/{$txt}.txt"));
        foreach ($rows as $row) {
            $row         = str_replace(chr(13), "", $row); //removes \r, not sure why chr(13) was used, but...
            $arrRow      = explode("\t", $row);
            $search_eams = $arrRow[0];
            if (is_numeric($search_eams)) { //skips title and any possible empty line
                $fields = [
                    'eams_ref_number' => $search_eams,
                    'firm_name'       => $arrRow[1],
                    'street_1'        => $arrRow[2],
                    'street_2'        => $arrRow[3],
                    'city'            => $arrRow[4],
                    'state'           => $arrRow[5],
                    'zip_code'        => $arrRow[6],
                    'phone'           => $arrRow[7],
                    'service_method'  => $arrRow[8],
                    'last_update'     => $arrRow[9],
                ];

                $result = DB::runOrDie("SELECT `$key` FROM `$table` WHERE `eams_ref_number` = ?", $search_eams);
                if ($result->rowCount() == 0) {
                    DB::insertOrDie($table, $fields);
                } else {
                    DB::updateOrDie($table, $fields, [$key => $result->fetchColumn()]);
                }
            }
        }
        die("$type update done:".date("m/d/Y H:i:s"));
    }

}
