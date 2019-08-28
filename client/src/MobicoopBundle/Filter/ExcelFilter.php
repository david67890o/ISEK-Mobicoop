<?php
/**
 * Created by PhpStorm.
 * User: vagrant
 * Date: 8/22/19
 * Time: 1:48 PM
 */

namespace Mobicoop\Bundle\MobicoopBundle\Filter;

use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

class ExcelFilter implements IReadFilter
{
    
    /**
     * Should this cell be read?
     *
     * @param string $column Column address (as a string value like "A", or "IV")
     * @param int $row Row number
     * @param string $worksheetName Optional worksheet name
     *
     * @return bool
     */
    public function readCell($column, $row, $worksheetName = '')
    {
        // TODO: Implement readCell() method.
        return $row>=1;
    }
}
