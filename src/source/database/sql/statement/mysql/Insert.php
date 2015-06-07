<?php
namespace chaos\source\database\sql\statement\mysql;

use chaos\SourceException;

/**
 * `INSERT` statement.
 */
class Insert extends \chaos\source\database\sql\statement\Insert
{
    /**
     * Sets `HIGH_PRIORITY` flag.
     *
     * @param  boolean $enable A boolan value.
     * @return object          Returns `$this`.
     */
    public function highPriority($enable = true)
    {
        $this->setFlag('HIGH_PRIORITY', $enable);
        return $this;
    }

    /**
     * Sets `LOW_PRIORITY` flag.
     *
     * @param  boolean $enable A boolan value.
     * @return object          Returns `$this`.
     */
    public function lowPriority($enable = true)
    {
        $this->setFlag('LOW_PRIORITY', $enable);
        return $this;
    }

    /**
     * Sets `IGNORE` flag.
     *
     * @param  boolean $enable A boolan value.
     * @return object          Returns `$this`.
     */
    public function ignore($enable = true)
    {
        $this->setFlag('IGNORE', $enable);
        return $this;
    }

    /**
     * Sets `DELAYED` flag.
     *
     * @param  boolean $enable A boolan value.
     * @return object          Returns `$this`.
     */
    public function delayed($enable = true)
    {
        $this->setFlag('DELAYED', $enable);
        return $this;
    }

}
