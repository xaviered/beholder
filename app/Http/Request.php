<?php
namespace App\Http;

use App\Database\Filters\FilterBase;
use Illuminate\Http\Request as BaseRequest;
use Illuminate\Database\Eloquent\Builder;

/**
 * Custom Request class that contains helper functions for the model controller
 *
 * @package App\Http
 */
class Request extends BaseRequest
{
	/** @var FilterBase[] */
	protected static $filters = [];

	/**
	 * @return array
	 */
	public function getFilters() {
		return static::$filters;
	}

	/**
	 * Given a filter class, will create new instance if not exists and add it
	 * to the filters with the key of its class.
	 *
	 * Retrieve the filter as
	 *  $filter = $request->getFilter($filterClass)
	 *
	 * @param string $filterClass
	 * @return FilterBase Newly added filter
	 */
	public function addFilter( $filterClass ) {
		if ( class_exists( $filterClass ) && !isset( static::$filters[ $filterClass ] ) ) {
			$this->setFilter( $filterClass, new $filterClass( $this ) );
		}

		return $this->getFilter( $filterClass );
	}

	/**
	 * Adds a new filter
	 *
	 * @param string $filterName
	 * @param FilterBase $filter
	 * @return $this Chainnable method
	 */
	public function setFilter( $filterName, FilterBase $filter ) {
		static::$filters[ $filterName ] = $filter;

		return $this;
	}

	/**
	 * Gets a filter by its name
	 *
	 * @param string $filterName
	 * @return FilterBase Null if not found
	 */
	public function getFilter( $filterName ) {
		return static::$filters[ $filterName ] ?? null;
	}

	/**
	 * Gets the filters to
	 * @param Builder $query
	 * @return Builder
	 */
	public function addFilters( Builder $query ) {
		foreach ( $this->query as $fieldName => $fieldValue ) {
			$logicalOperator = 'eq';
			// get custom logical operator
			if ( strpos( $fieldName, ':' ) ) {
				list( $fieldName, $logicalOperator ) = explode( ':', $fieldName, 2 );
			}

			if ( in_array( $fieldName, [ 'page', 'page_size' ] ) ) {
				continue;
			}

			// turn list to array
			if ( strpos( $fieldValue, ',' ) ) {
				$fieldValue = explode( ',', $fieldValue );
			}

			$query = $this->addWhereClause( $query, $fieldName, $logicalOperator, $fieldValue );
		}

		return $query;
	}

	/**
	 * Given a string, will get the DB operator for it
	 *
	 *
	 * @param Builder $query
	 * @param string $fieldName
	 * @param string $logicalOperator
	 * @param mixed $fieldValue
	 * @return mixed
	 */
	protected function addWhereClause( Builder $query, $fieldName, $logicalOperator, &$fieldValue ) {

		switch ( strtolower( $logicalOperator ) ) {
			case 'eq':
			default:
				$query->where( $fieldName, $fieldValue );
				break;
			case 'not':
				$query->whereNotIn( $fieldName, (array)$fieldValue );
				break;

			case 'in':
				$query->whereIn( $fieldName, (array)$fieldValue );
				break;
			case 'not-in':
				$query->whereNotIn( $fieldName, (array)$fieldValue )
					->whereNotNull( $fieldName )
				;
				break;

			case 'like':
				$query->where( $fieldName, 'like', '%' . $fieldValue . '%' );
				break;
			case 'not-like':
				$query->where( $fieldName, 'not regexp', '/.*' . $fieldValue . '.*/' );
				break;


			case 'gt':
				$query->where( $fieldName, '>', intval( $fieldValue ) );
				break;
			case 'lt':
				$query->where( $fieldName, '<', intval( $fieldValue ) );
				break;

			case 'between':
				$fieldValue = (array)$fieldValue;
				array_walk( $fieldValue, function( &$ele ) { $ele = intval( $ele ); } );
				$query->whereBetween( $fieldName, $fieldValue );
				break;
		}

		return $query;
	}
}
