<?php
declare(strict_types=1);

namespace BootstrapUI\Test\TestCase\View\Helper;

use BootstrapUI\View\Helper\PaginatorHelper;
use Cake\Core\Configure;
use Cake\Datasource\Paging\PaginatedResultSet;
use Cake\Http\ServerRequest;
use Cake\I18n\I18n;
use Cake\ORM\ResultSet;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use Cake\View\View;

/**
 * PaginatorHelperTest class
 */
class PaginatorHelperTest extends TestCase
{
    /**
     * @var \Cake\View\View
     */
    public $View;

    /**
     * @var \BootstrapUI\View\Helper\PaginatorHelper
     */
    public $Paginator;

    /**
     * @var string
     */
    public $locale;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        Configure::write('Config.language', 'eng');

        $request = new ServerRequest([
            'params' => [
                'plugin' => null,
                'controller' => 'clients',
                'action' => 'index',
                '_ext' => null,
                'pass' => [],
            ],
        ]);

        $this->View = new View($request);
        $this->Paginator = new PaginatorHelper($this->View);

        Configure::write('Routing.prefixes', []);
        Router::reload();
        $builder = Router::createRouteBuilder('/');
        $builder->connect('/{controller}/{action}/*');
        Router::setRequest($request);

        $this->locale = I18n::getLocale();
    }

    /**
     * Sets the paginated result the helper renders from.
     *
     * CakePHP 5.1+ reads pagination from a PaginatedInterface set via
     * `setPaginated()` rather than from the request's `paging` param.
     *
     * @param array $params Paging metadata.
     * @return void
     */
    protected function setPaginated(array $params): void
    {
        $params += [
            'alias' => 'Clients',
            'currentPage' => 1,
            'count' => 0,
            'totalCount' => 0,
            'perPage' => 20,
            'pageCount' => 1,
            'hasPrevPage' => false,
            'hasNextPage' => false,
            'sort' => null,
            'direction' => null,
        ];

        $this->Paginator->setPaginated(new PaginatedResultSet(new ResultSet([]), $params));
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->View, $this->Paginator);

        I18n::setLocale($this->locale);
    }

    /**
     * testLinks method
     *
     * @return void
     */
    public function testLinks()
    {
        $this->setPaginated([
            'currentPage' => 8,
            'count' => 3,
            'totalCount' => 30,
            'pageCount' => 15,
            'hasPrevPage' => false,
            'hasNextPage' => true,
        ]);
        $result = $this->Paginator->links();
        $expected = [
            'ul' => ['class' => 'pagination'],
            '<li', ['a' => ['href' => '/clients/index?page=4']], '4', '/a', '/li',
            '<li', ['a' => ['href' => '/clients/index?page=5']], '5', '/a', '/li',
            '<li', ['a' => ['href' => '/clients/index?page=6']], '6', '/a', '/li',
            '<li', ['a' => ['href' => '/clients/index?page=7']], '7', '/a', '/li',
            ['li' => ['class' => 'active']], '<span', '8', 'span' => ['class' => 'sr-only'], '(current)', '/span', '/span', '/li',
            '<li', ['a' => ['href' => '/clients/index?page=9']], '9', '/a', '/li',
            '<li', ['a' => ['href' => '/clients/index?page=10']], '10', '/a', '/li',
            '<li', ['a' => ['href' => '/clients/index?page=11']], '11', '/a', '/li',
            '<li', ['a' => ['href' => '/clients/index?page=12']], '12', '/a', '/li',
            '/ul',
        ];
        $this->assertHtml($expected, $result);

        $this->setPaginated([
            'currentPage' => 8,
            'count' => 3,
            'totalCount' => 30,
            'pageCount' => 15,
            'hasPrevPage' => false,
            'hasNextPage' => true,
        ]);
        $result = $this->Paginator->links(['prev' => true, 'next' => true]);
        $expected = [
            'ul' => ['class' => 'pagination'],
            ['li' => ['class' => 'previous disabled']], ['a' => []], ['span' => ['aria-hidden' => 'true']], '&laquo;', '/span', '/a', '/li',
            '<li', ['a' => ['href' => '/clients/index?page=4']], '4', '/a', '/li',
            '<li', ['a' => ['href' => '/clients/index?page=5']], '5', '/a', '/li',
            '<li', ['a' => ['href' => '/clients/index?page=6']], '6', '/a', '/li',
            '<li', ['a' => ['href' => '/clients/index?page=7']], '7', '/a', '/li',
            ['li' => ['class' => 'active']], '<span', '8', 'span' => ['class' => 'sr-only'], '(current)', '/span', '/span', '/li',
            '<li', ['a' => ['href' => '/clients/index?page=9']], '9', '/a', '/li',
            '<li', ['a' => ['href' => '/clients/index?page=10']], '10', '/a', '/li',
            '<li', ['a' => ['href' => '/clients/index?page=11']], '11', '/a', '/li',
            '<li', ['a' => ['href' => '/clients/index?page=12']], '12', '/a', '/li',
            ['li' => ['class' => 'next']], ['a' => ['rel' => 'next', 'aria-label' => 'Next', 'href' => '/clients/index?page=9']], ['span' => ['aria-hidden' => 'true']], '&raquo;', '/span', '/a', '/li',
            '/ul',
        ];
        $this->assertHtml($expected, $result);

        $this->setPaginated([
            'currentPage' => 1,
            'count' => 1,
            'totalCount' => 2,
            'pageCount' => 2,
            'hasPrevPage' => false,
            'hasNextPage' => true,
        ]);
        $result = $this->Paginator->links(['size' => 'lg']);
        $expected = [
            'ul' => ['class' => 'pagination pagination-lg'],
            ['li' => ['class' => 'active']], '<span', '1', 'span' => ['class' => 'sr-only'], '(current)', '/span', '/span', '/li',
            '<li', ['a' => ['href' => '/clients/index?page=2']], '2', '/a', '/li',
            '/ul',
        ];
        $this->assertHtml($expected, $result);

        $result = $this->Paginator->links(['size' => 'sx']);
        $this->assertFalse($result);
    }
}
