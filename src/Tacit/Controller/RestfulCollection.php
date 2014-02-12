<?php
/*
 * This file is part of the Tacit package.
 *
 * Copyright (c) Jason Coward <jason@opengeek.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tacit\Controller;


use Tacit\Controller\Exception\ServerErrorException;
use Tacit\Controller\Exception\UnacceptableEntityException;
use Tacit\Model\Exception\ModelValidationException;
use Tacit\Model\Query;

abstract class RestfulCollection extends Restful
{
    public function get()
    {
        $limit = $this->app->request->get('limit', 25);
        $offset = $this->app->request->get('offset', 0);
        $orderBy = $this->app->request->get('sort', 'created_at');
        $orderDir = $this->app->request->get('sort_dir', 'desc');

        /** @var \Tacit\Model\Persistent $modelClass */
        $modelClass = static::$modelClass;

        try {
            $total = $modelClass::count();

            $collection = $modelClass::find(function ($query) use ($offset, $limit, $orderBy, $orderDir) {
                /** @var Query $query */
                $query->orderBy($orderBy, $orderDir)->skip($offset)->limit($limit);
            });
        } catch (\Exception $e) {
            throw new ServerErrorException($this, null, null, null, $e);
        }

        $this->respondWithCollection($collection, $modelClass::transformer(), ['total' => $total]);
    }

    public function options()
    {
        /* @var \Slim\Http\Response $response */
        $response = $this->app->response;
        $response->headers->set('Content-Type', static::$responseType);
        $response->setStatus(200);
        if ($this->app->config('debug') === true) {
            $resource['request_duration'] = microtime(true) - $this->app->config('startTime');
        }
        $response->headers->set('Allow', implode(',', ['OPTIONS', 'HEAD', 'GET', 'POST']));

        $this->app->stop();
    }

    public function post()
    {
        /** @var \Tacit\Model\Persistent $modelClass */
        $modelClass = static::$modelClass;

        try {
            $item = $modelClass::create($this->app->request->post(null, []));
        } catch (ModelValidationException $e) {
            throw new UnacceptableEntityException($this, 'Resource validation failed', $e->getMessage(), $e->getMessages(), $e);
        } catch (\Exception $e) {
            throw new ServerErrorException($this, null, null, null, $e);
        }

        $this->respondWithItemCreated($item, $modelClass::transformer());
    }
}