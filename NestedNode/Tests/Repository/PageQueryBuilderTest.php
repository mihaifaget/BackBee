<?php

/*
 * Copyright (c) 2011-2015 Lp digital system
 *
 * This file is part of BackBee.
 *
 * BackBee is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * BackBee is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with BackBee. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Charles Rouillon <charles.rouillon@lp-digital.fr>
 */

namespace BackBee\NestedNode\Tests\Repository;

use BackBee\NestedNode\Page;
use BackBee\NestedNode\Repository\PageQueryBuilder;
use BackBee\Site\Site;
use BackBee\Tests\BackBeeTestCase;

/**
 * @category    BackBee
 *
 * @copyright   Lp digital system
 * @author      c.rouillon <charles.rouillon@lp-digital.fr>
 */
class PageQueryBuilderTest extends BackBeeTestCase
{
    /**
     * @var string
     */
    private static $previousDateFormat;

    /**
     * @var \BackBee\NestedNode\Repository\PageRepository
     */
    private $repository;

    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->repository = self::$em->getRepository('BackBee\NestedNode\Page');
    }

    public static function setUpBeforeClass()
    {
        self::$kernel->resetDatabase();

        self::$previousDateFormat = PageQueryBuilder::$config['dateSchemeForPublishing'];

        PageQueryBuilder::$config = array(
            'dateSchemeForPublishing' => 'Y-m-d H:i:00',
        );
    }

    /**
     * @covers \BackBee\NestedNode\Repository\PageQueryBuilder::hasJoinCriteria
     */
    public function testHasJoinCriteria()
    {
        $this->assertFalse(PageQueryBuilder::hasJoinCriteria());
        $this->assertFalse(PageQueryBuilder::hasJoinCriteria(array('_unknown' => 'fake')));
        $this->assertTrue(PageQueryBuilder::hasJoinCriteria(array('_root' => 'fake')));
        $this->assertTrue(PageQueryBuilder::hasJoinCriteria(array('_parent' => 'fake')));
        $this->assertTrue(PageQueryBuilder::hasJoinCriteria(array('_leftnode' => 'fake')));
        $this->assertTrue(PageQueryBuilder::hasJoinCriteria(array('_rightnode' => 'fake')));
        $this->assertTrue(PageQueryBuilder::hasJoinCriteria(array('_site' => 'fake')));
    }

    /**
     * @covers \BackBee\NestedNode\Repository\PageQueryBuilder::andSiteIs
     */
    public function testAndSiteIs()
    {
        $site = new Site();
        $q = $this->repository->createQueryBuilder('p')
                ->andSiteIs($site);

        $this->assertInstanceOf('BackBee\NestedNode\Repository\PageQueryBuilder', $q);
        $this->assertEquals('SELECT p FROM BackBee\NestedNode\Page p INNER JOIN p._section p_s WHERE p_s._site = :site0', $q->getDql());
        $this->assertEquals($site, $q->getParameter('site0')->getValue());
    }

    /**
     * @covers \BackBee\NestedNode\Repository\PageQueryBuilder::andIsSection
     */
    public function testAndIsSection()
    {
        $q = $this->repository->createQueryBuilder('p')
                ->andIsSection();

        $this->assertInstanceOf('BackBee\NestedNode\Repository\PageQueryBuilder', $q);
        $this->assertEquals('SELECT p FROM BackBee\NestedNode\Page p WHERE p._section = p', $q->getDql());
    }

    /**
     * @covers \BackBee\NestedNode\Repository\PageQueryBuilder::andIsNotSection
     */
    public function testAndIsNotSection()
    {
        $q = $this->repository->createQueryBuilder('p')
                ->andIsNotSection();

        $this->assertInstanceOf('BackBee\NestedNode\Repository\PageQueryBuilder', $q);
        $this->assertEquals('SELECT p FROM BackBee\NestedNode\Page p WHERE p._section != p', $q->getDql());
    }

    /**
     * @covers \BackBee\NestedNode\Repository\PageQueryBuilder::andIsOnline
     */
    public function testAndIsOnline()
    {
        $q = $this->repository->createQueryBuilder('p')
                ->andIsOnline();

        $this->assertInstanceOf('BackBee\NestedNode\Repository\PageQueryBuilder', $q);
        $this->assertEquals('SELECT p FROM BackBee\NestedNode\Page p WHERE p._state IN (1,3) AND (p._publishing IS NULL OR p._publishing <= \''.date(PageQueryBuilder::$config['dateSchemeForPublishing'], time()).'\') AND (p._archiving IS NULL OR p._archiving > \''.date(PageQueryBuilder::$config['dateSchemeForPublishing'], time()).'\')', $q->getDql());

        PageQueryBuilder::$config['dateSchemeForPublishing'] = 'Y-m-d H:i:s';
        $q->resetDQLPart('where')
                ->andIsOnline();
        $this->assertEquals('SELECT p FROM BackBee\NestedNode\Page p WHERE p._state IN (1,3) AND (p._publishing IS NULL OR p._publishing <= \''.date(PageQueryBuilder::$config['dateSchemeForPublishing'], time()).'\') AND (p._archiving IS NULL OR p._archiving > \''.date(PageQueryBuilder::$config['dateSchemeForPublishing'], time()).'\')', $q->getDql());
        PageQueryBuilder::$config['dateSchemeForPublishing'] = 'Y-m-d H:i:00';
    }

    /**
     * @covers \BackBee\NestedNode\Repository\PageQueryBuilder::andIsNotDeleted
     */
    public function testAndIsNotDeleted()
    {
        $q = $this->repository->createQueryBuilder('p')
                ->andIsNotDeleted();

        $this->assertInstanceOf('BackBee\NestedNode\Repository\PageQueryBuilder', $q);
        $this->assertEquals('SELECT p FROM BackBee\NestedNode\Page p WHERE p._state < 4', $q->getDql());
    }

    /**
     * @covers \BackBee\NestedNode\Repository\PageQueryBuilder::andIsVisible
     */

    public function testAndIsVisible()
    {
        $q = $this->repository->createQueryBuilder('p')
                ->andIsVisible();

        $this->assertInstanceOf('BackBee\NestedNode\Repository\PageQueryBuilder', $q);
        $this->assertEquals('SELECT p FROM BackBee\NestedNode\Page p WHERE p._state = 1 AND (p._publishing IS NULL OR p._publishing <= \''.date(PageQueryBuilder::$config['dateSchemeForPublishing'], time()).'\') AND (p._archiving IS NULL OR p._archiving > \''.date(PageQueryBuilder::$config['dateSchemeForPublishing'], time()).'\')', $q->getDql());
    }

    /**
     * @covers \BackBee\NestedNode\Repository\PageQueryBuilder::andIsAncestorOf
     */
    public function testAndIsAncestorOf()
    {
        $page = new Page('test');
        $q = $this->repository->createQueryBuilder('p')
                ->andIsAncestorOf($page);

        $this->assertInstanceOf('BackBee\NestedNode\Repository\PageQueryBuilder', $q);

        $this->assertEquals('SELECT p FROM BackBee\NestedNode\Page p INNER JOIN p._section p_s WHERE p._section = p AND p_s._root = :root0 AND p_s._leftnode <= :leftnode0 AND p_s._rightnode >= :rightnode0', $q->getDql());
        $this->assertEquals($page->getSection()->getRoot(), $q->getParameter('root0')->getValue());
        $this->assertEquals($page->getSection()->getLeftnode(), $q->getParameter('leftnode0')->getValue());
        $this->assertEquals($page->getSection()->getRightnode(), $q->getParameter('rightnode0')->getValue());

        $q->resetDQLPart('where')
                ->setParameters(array())
                ->andIsAncestorOf($page, true);
        $this->assertEquals($page->getSection()->getLeftnode() - 1, $q->getParameter('leftnode0')->getValue());
        $this->assertEquals($page->getSection()->getRightnode() + 1, $q->getParameter('rightnode0')->getValue());

        $q->resetDQLPart('where')
                ->setParameters(array())
                ->andIsAncestorOf($page, true, 1);
        $this->assertEquals('SELECT p FROM BackBee\NestedNode\Page p INNER JOIN p._section p_s WHERE p._section = p AND p_s._root = :root0 AND p_s._leftnode <= :leftnode0 AND p_s._rightnode >= :rightnode0 AND p_s._level = :level0', $q->getDql());
        $this->assertEquals(1, $q->getParameter('level0')->getValue());
    }

    /**
     * @covers \BackBee\NestedNode\Repository\PageQueryBuilder::andRootIs
     */
    public function testAndRootIs()
    {
        $root = new Page('root');

        $q = $this->repository->createQueryBuilder('p')
                ->andRootIs($root);

        $this->assertInstanceOf('BackBee\NestedNode\Repository\PageQueryBuilder', $q);
        $this->assertEquals('SELECT p FROM BackBee\NestedNode\Page p INNER JOIN p._section p_s WHERE p_s._root = :root0', $q->getDql());
        $this->assertEquals($root->getSection(), $q->getParameter('root0')->getValue());
    }

    /**
     * @covers \BackBee\NestedNode\Repository\PageQueryBuilder::andRootIs
     * @expectedException \InvalidArgumentException
     */
    public function testAndRootIsWithInvalidRoot()
    {
        $root = new Page('root');
        $child = new Page('child');
        $child->setSection($root->getSection());

        $this->repository->createQueryBuilder('p')
                ->andRootIs($child);
    }

    /**
     * @covers \BackBee\NestedNode\Repository\PageQueryBuilder::andParentIs
     */
    public function testParentIs()
    {
        $root = new Page('root');
        $child = new Page('child');
        $child->setSection($root->getSection());

        $q = $this->repository->createQueryBuilder('p')
                ->andParentIs();

        $this->assertInstanceOf('BackBee\NestedNode\Repository\PageQueryBuilder', $q);
        $this->assertEquals('SELECT p FROM BackBee\NestedNode\Page p INNER JOIN p._section p_s WHERE p_s._parent IS NULL AND p_s = p', $q->getDql());

        $q->resetDQLPart('where')
                ->andParentIs($child);
        $this->assertEquals('SELECT p FROM BackBee\NestedNode\Page p INNER JOIN p._section p_s WHERE 1 = 0', $q->getDql());

        $q->resetDQLPart('where')
                ->andParentIs($root);
        $this->assertEquals('SELECT p FROM BackBee\NestedNode\Page p INNER JOIN p._section p_s WHERE (p_s._parent = :parent0 OR p._section = :parent0) AND p._level = :level0', $q->getDql());
        $this->assertEquals($root->getSection(), $q->getParameter('parent0')->getValue());
        $this->assertEquals($root->getLevel() + 1, $q->getParameter('level0')->getValue());
    }

    /**
     * @covers \BackBee\NestedNode\Repository\PageQueryBuilder::andIsSiblingsOf
     */
    public function testAndIsSiblingOf()
    {
        $root = new Page('root');
        $child = new Page('child');
        $child->setParent($root);

        $q = $this->repository->createQueryBuilder('p')

                ->andIsSiblingsOf($root);

        $this->assertInstanceOf('BackBee\NestedNode\Repository\PageQueryBuilder', $q);

        $this->assertEquals('SELECT p FROM BackBee\NestedNode\Page p INNER JOIN p._section p_s WHERE p_s._parent IS NULL AND p_s = p', $q->getDql());

        $q->resetDQLPart('where')
                ->andIsSiblingsOf($child);
        $this->assertEquals('SELECT p FROM BackBee\NestedNode\Page p INNER JOIN p._section p_s WHERE p_s._root = :root0 AND (p_s._leftnode BETWEEN 1 AND 2) AND p._level <= :level0', $q->getDql());
        $this->assertEquals($child->getSection()->getRoot(), $q->getParameter('root0')->getValue());
        $this->assertEquals($child->getLevel(), $q->getParameter('level0')->getValue());

        $q->resetDQLPart('where')
                ->setParameters(array())
                ->andIsSiblingsOf($child, true);
        $this->assertEquals('SELECT p FROM BackBee\NestedNode\Page p INNER JOIN p._section p_s WHERE p_s._root = :root0 AND (p_s._leftnode BETWEEN 1 AND 2) AND p._level <= :level0 AND p != :page2', $q->getDql());
        $this->assertEquals($child, $q->getParameter('page2')->getValue());

        $q->resetDQLPart('where')
                ->setParameters(array())
                ->andIsSiblingsOf($child, false, array('p._position' => 'ASC'));
        $this->assertEquals('SELECT p FROM BackBee\NestedNode\Page p INNER JOIN p._section p_s WHERE p_s._root = :root0 AND (p_s._leftnode BETWEEN 1 AND 2) AND p._level <= :level0 ORDER BY p._position ASC', $q->getDql());

        $q->andIsSiblingsOf($child, false, null, 10);
        $this->assertEquals(10, $q->getMaxResults());
        $this->assertEquals(0, $q->getFirstResult());

        $q->andIsSiblingsOf($child, false, null, 10, 1);
        $this->assertEquals(10, $q->getMaxResults());
        $this->assertEquals(1, $q->getFirstResult());
    }

    /**
     * @covers \BackBee\NestedNode\Repository\PageQueryBuilder::andIsDescendantOf
     */

    public function testAndIsDescendantOf()
    {
        $page = new Page('test');
        $q = $this->repository->createQueryBuilder('p')

                ->andIsDescendantOf($page);

        $this->assertInstanceOf('BackBee\NestedNode\Repository\PageQueryBuilder', $q);

        $this->assertEquals('SELECT p FROM BackBee\NestedNode\Page p INNER JOIN p._section p_s WHERE p_s._root = :root0 AND (p_s._leftnode BETWEEN 1 AND 2)', $q->getDql());
        $this->assertEquals($page->getSection()->getRoot(), $q->getParameter('root0')->getValue());

        $q->resetDQLPart('where')
                ->setParameters(array())
                ->andIsDescendantOf($page, true);
        $this->assertEquals('SELECT p FROM BackBee\NestedNode\Page p INNER JOIN p._section p_s WHERE p_s._root = :root0 AND (p_s._leftnode BETWEEN 1 AND 2) AND p != :page0', $q->getDql());
        $this->assertEquals($page, $q->getParameter('page0')->getValue());

        $q->resetDQLPart('where')
                ->setParameters(array())
                ->andIsDescendantOf($page, false, 1);
        $this->assertEquals('SELECT p FROM BackBee\NestedNode\Page p INNER JOIN p._section p_s WHERE p_s._root = :root0 AND (p_s._leftnode BETWEEN 1 AND 2) AND p._level <= :level0', $q->getDql());
        $this->assertEquals(1, $q->getParameter('level0')->getValue());

        $q->resetDQLPart('where')
                ->setParameters(array())
                ->andIsDescendantOf($page, false, 1, null, null, 0, true);
        $this->assertEquals('SELECT p FROM BackBee\NestedNode\Page p INNER JOIN p._section p_s WHERE p_s._root = :root0 AND (p_s._leftnode BETWEEN 1 AND 2) AND p._level <= :level0 AND p._section = p', $q->getDql());

        $q->resetDQLPart('where')
                ->setParameters(array())
                ->andIsDescendantOf($page, false, 1, null, 10);
        $this->assertEquals(10, $q->getMaxResults());
        $this->assertEquals(0, $q->getFirstResult());

        $q->resetDQLPart('where')
                ->setParameters(array())
                ->andIsDescendantOf($page, false, 1, null, 10, 2);
        $this->assertEquals(10, $q->getMaxResults());
        $this->assertEquals(2, $q->getFirstResult());

        $q->resetDQLPart('where')
                ->setParameters(array())
                ->andIsDescendantOf($page, false, 1, array('_leftnode' => 'ASC'));
        $this->assertEquals('SELECT p FROM BackBee\NestedNode\Page p INNER JOIN p._section p_s WHERE p_s._root = :root0 AND (p_s._leftnode BETWEEN 1 AND 2) AND p._level <= :level0 ORDER BY p_s._leftnode ASC', $q->getDql());
    }

    /**
     * @covers \BackBee\NestedNode\Repository\PageQueryBuilder::andStateIsIn
     */
    public function testAndStateIsIn()
    {
        $q = $this->repository->createQueryBuilder('p')
                ->andStateIsIn(Page::STATE_ONLINE);

        $this->assertInstanceOf('BackBee\NestedNode\Repository\PageQueryBuilder', $q);
        $this->assertEquals('SELECT p FROM BackBee\NestedNode\Page p WHERE p._state IN (:states0)', $q->getDql());
        $this->assertEquals(array(Page::STATE_ONLINE), $q->getParameter('states0')->getValue());

        $q->resetDQLPart('where')
                ->setParameters(array())
                ->andStateIsIn(array(Page::STATE_ONLINE));

        $this->assertEquals('SELECT p FROM BackBee\NestedNode\Page p WHERE p._state IN (:states0)', $q->getDql());
        $this->assertEquals(array(Page::STATE_ONLINE), $q->getParameter('states0')->getValue());
    }

    /**
     * @covers \BackBee\NestedNode\Repository\PageQueryBuilder::andStateIsNotIn
     */
    public function testAndStateIsNotIn()
    {
        $q = $this->repository->createQueryBuilder('p')
                ->andStateIsNotIn(Page::STATE_ONLINE);

        $this->assertInstanceOf('BackBee\NestedNode\Repository\PageQueryBuilder', $q);
        $this->assertEquals('SELECT p FROM BackBee\NestedNode\Page p WHERE p._state NOT IN (:states0)', $q->getDql());
        $this->assertEquals(array(Page::STATE_ONLINE), $q->getParameter('states0')->getValue());

        $q->resetDQLPart('where')
                ->setParameters(array())
                ->andStateIsNotIn(array(Page::STATE_ONLINE, Page::STATE_OFFLINE));

        $this->assertEquals('SELECT p FROM BackBee\NestedNode\Page p WHERE p._state NOT IN (:states0)', $q->getDql());
        $this->assertEquals(array(Page::STATE_ONLINE, Page::STATE_OFFLINE), $q->getParameter('states0')->getValue());
    }

    /**
     * @covers \BackBee\NestedNode\Repository\PageQueryBuilder::andSearchCriteria
     */
    public function testAndSearchCriteria()
    {
        $now = new \DateTime();

        $q = $this->repository->createQueryBuilder('p')
                ->andSearchCriteria('fake');
        $this->assertInstanceOf('BackBee\NestedNode\Repository\PageQueryBuilder', $q);
        $this->assertEquals('SELECT p FROM BackBee\NestedNode\Page p', $q->getDql());

        $q->resetDQLPart('where')
                ->andSearchCriteria(array(), 'fake');
        $this->assertInstanceOf('BackBee\NestedNode\Repository\PageQueryBuilder', $q);
        $this->assertEquals('SELECT p FROM BackBee\NestedNode\Page p', $q->getDql());

        $q->resetDQLPart('where')
                ->andSearchCriteria(array('all'));
        $this->assertInstanceOf('BackBee\NestedNode\Repository\PageQueryBuilder', $q);
        $this->assertEquals('SELECT p FROM BackBee\NestedNode\Page p', $q->getDql());

        $q->resetDQLPart('where')
                ->andSearchCriteria(array(), array('beforePubdateField' => $now->getTimestamp()));
        $this->assertInstanceOf('BackBee\NestedNode\Repository\PageQueryBuilder', $q);
        $this->assertEquals('SELECT p FROM BackBee\NestedNode\Page p WHERE p._modified < :date0', $q->getDql());

        $q->resetDQLPart('where')
                ->andSearchCriteria(array(), array('afterPubdateField' => $now->getTimestamp()));
        $this->assertInstanceOf('BackBee\NestedNode\Repository\PageQueryBuilder', $q);
        $this->assertEquals('SELECT p FROM BackBee\NestedNode\Page p WHERE p._modified > :date1', $q->getDql());

        $q->resetDQLPart('where')
                ->andSearchCriteria(array(Page::STATE_ONLINE));
        $this->assertInstanceOf('BackBee\NestedNode\Repository\PageQueryBuilder', $q);
        $this->assertEquals('SELECT p FROM BackBee\NestedNode\Page p WHERE p._state IN (:states2)', $q->getDql());

        $q->resetDQLPart('where')
                ->andSearchCriteria(array(), array('searchField' => 'test'));
        $this->assertInstanceOf('BackBee\NestedNode\Repository\PageQueryBuilder', $q);
        $this->assertEquals("SELECT p FROM BackBee\NestedNode\Page p WHERE p._title LIKE '%test%'", $q->getDql());
    }

    /**
     * @covers \BackBee\NestedNode\Repository\PageQueryBuilder::addSearchCriteria
     */
    public function testAddSearchCriteria()
    {
        $q = $this->repository->createQueryBuilder('p')
                ->addSearchCriteria(array('_uid' => 'test'));

        $this->assertInstanceOf('BackBee\NestedNode\Repository\PageQueryBuilder', $q);
        $this->assertEquals('SELECT p FROM BackBee\NestedNode\Page p WHERE p._uid IN (:p__uid0)', $q->getDql());
        $this->assertEquals('test', $q->getParameter('p__uid0')->getValue());

        $q->resetDQLPart('where')
                ->setParameters(array())
                ->addSearchCriteria(array('_leftnode' => 1));
        $this->assertEquals('SELECT p FROM BackBee\NestedNode\Page p INNER JOIN p._section p_s WHERE p_s._leftnode IN (:p_s__leftnode0)', $q->getDql());
        $this->assertEquals('1', $q->getParameter('p_s__leftnode0')->getValue());
    }

    /**
     * @covers \BackBee\NestedNode\Repository\PageQueryBuilder::addOrderBy
     */
    public function testAddOrderBy()
    {
        $q = $this->repository->createQueryBuilder('p')
                ->addOrderBy('_position');

        $this->assertInstanceOf('BackBee\NestedNode\Repository\PageQueryBuilder', $q);
        $this->assertEquals('SELECT p FROM BackBee\NestedNode\Page p ORDER BY p._position ASC', $q->getDql());

        $q->resetDQLPart('orderBy')
                ->addOrderBy('_position', 'DESC');
        $this->assertEquals('SELECT p FROM BackBee\NestedNode\Page p ORDER BY p._position DESC', $q->getDql());

        $q->resetDQLPart('orderBy')
                ->addOrderBy('_leftnode');
        $this->assertEquals('SELECT p FROM BackBee\NestedNode\Page p INNER JOIN p._section p_s ORDER BY p_s._leftnode ASC', $q->getDql());
    }

    /**
     * @covers \BackBee\NestedNode\Repository\PageQueryBuilder::addMultipleOrderBy
     */
    public function testAddMultipleOrderBy()
    {
        $q = $this->repository->createQueryBuilder('p')
                ->addMultipleOrderBy();
        $this->assertInstanceOf('BackBee\NestedNode\Repository\PageQueryBuilder', $q);
        $this->assertEquals('SELECT p FROM BackBee\NestedNode\Page p ORDER BY p._position ASC', $q->getDql());

        $q->addMultipleOrderBy(array('_title' => 'ASC', '_leftnode' => 'DESC'));
        $this->assertEquals('SELECT p FROM BackBee\NestedNode\Page p INNER JOIN p._section p_s ORDER BY p._position ASC, p._title ASC, p_s._leftnode DESC', $q->getDql());
    }

    /**
     * @covers \BackBee\NestedNode\Repository\PageQueryBuilder::orderByMultiple
     */
    public function testOrderByMultiple()
    {
        $q = $this->repository->createQueryBuilder('p')->orderByMultiple(['_title' => 'DESC']);
        $this->assertEquals('SELECT p FROM BackBee\NestedNode\Page p ORDER BY p._title DESC', $q->getDql());
        
        $q->orderByMultiple();
        $this->assertInstanceOf('BackBee\NestedNode\Repository\PageQueryBuilder', $q);
        $this->assertEquals('SELECT p FROM BackBee\NestedNode\Page p INNER JOIN p._section p_s ORDER BY p._position ASC, p_s._leftnode ASC', $q->getDql());
        
        $q->orderByMultiple([]);
        $this->assertEquals('SELECT p FROM BackBee\NestedNode\Page p INNER JOIN p._section p_s ORDER BY p._position ASC, p_s._leftnode ASC', $q->getDql());
    }

    /**
     * @covers \BackBee\NestedNode\Repository\PageQueryBuilder::getAlias
     */
    public function testGetAlias()
    {
        $q = $this->repository->createQueryBuilder('p');
        $this->assertEquals('p', $q->getAlias());
    }

    /**
     * @covers \BackBee\NestedNode\Repository\PageQueryBuilder::getSectionAlias
     */
    public function testGetSectionAlias()
    {
        $q = $this->repository->createQueryBuilder('p');
        $this->assertEquals(0, count($q->getDQLPart('join')));
        $this->assertEquals('p_s', $q->getSectionAlias());
        $this->assertEquals(1, count($q->getDQLPart('join')));
    }

    /**
     * Sets up the fixture.
     */
    public function setUp()
    {
        self::$em->clear();

        self::$kernel->resetDatabase([
            self::$em->getClassMetaData('BackBee\NestedNode\Page'),
        ]);
    }

    public static function tearDownAfterClass()
    {
        PageQueryBuilder::$config = array(
            'dateSchemeForPublishing' => self::$previousDateFormat,
        );
    }
}
