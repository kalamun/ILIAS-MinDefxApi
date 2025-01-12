<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Class ilCmiXapiAbstractReportLinkBuilder
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 * @author      Roberto Kalamun Pasini <rp@kalamun.net>
 * edited by MinDefxAPI v8.12
 *
 * @package     Module/CmiXapi
 */
abstract class ilCmiXapiAbstractReportLinkBuilder
{
    protected int $objId;
    protected string $aggregateEndPoint;
    protected ilCmiXapiStatementsReportFilter $filter;

    /**
     * ilCmiXapiAbstractReportLinkBuilder constructor.
     */
    public function __construct(
        int $objId,
        string $aggregateEndPoint,
        ilCmiXapiStatementsReportFilter $filter
    ) {
        $this->objId = $objId;
        $this->aggregateEndPoint = $aggregateEndPoint;
        $this->filter = $filter;
    }

    public function getUrl(): string
    {
        return $this->appendRequestParameters($this->aggregateEndPoint);
    }

    //todo ilUtil
    protected function appendRequestParameters(string $url): string
    {
        return ilUtil::appendUrlParameterString($url, $this->buildPipelineParameter());
    }

    protected function buildPipelineParameter(): string
    {
        return $this->buildPipeline()[0];
    }

    /**
     * @return mixed[]
     */
    abstract protected function buildPipeline(): array;

    public function getObjId(): int
    {
        return $this->objId;
    }

    public function getAggregateEndPoint(): string
    {
        return $this->aggregateEndPoint;
    }

    /**
    * @return ilObjCmiXapi|ilObjLTIConsumer
    */
    public function getObj()
    {
        if (ilObject::_lookupType($this->getObjId()) == 'lti') {
            return ilObjLTIConsumer::getInstance($this->getObjId(), false);
        }
        return ilObjCmiXapi::getInstance($this->getObjId(), false);
    }
}
