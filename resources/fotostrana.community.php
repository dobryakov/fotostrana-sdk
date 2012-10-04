<?php

class fotostranaCommunity extends fotostranaObject
{

    function __construct($community_id)
    {
        $this->data['forum']['community_id']=$community_id;
    }

    function loadData()
    {

        /** @var $r fotostranaRequest */

        // загружаем информацию о форуме
        $r = $this->request();
        $r->setMethod('ForumCommunity.getForum');
        $r->setParam('communityId', $this->data['forum']['community_id']);
        $apiresult = $r->get();
        if ($apiresult && isset($apiresult['response'])) {
            $this->data['forum'] = array_merge($this->data['forum'], $apiresult['response']);
        }

        // загружаем информацию о самом коммьюнити
        $r->setMethod('Community.getInfo');
        $r->setParam('communityIds', $this->data['forum']['community_id']);
        $r->setParam('fields', 'name,description,count,foto_48,foto_97,foto_192');
        $apiresult = $r->get();
        if (isset($apiresult['response']) && is_array($apiresult['response'])) {
            $community = reset($apiresult['response']);
            $this->data = array_merge($this->data, $community);
        }

    }

    function __get($name) {
        switch ($name) {
            case 'id':
                return $this->data['forum']['community_id'];
                break;
            default:
                return parent::__get($name);
        }
    }

    function forum()
    {
        /** @var $r fotostranaRequest */
        if (!isset($this->data['forum']['forum_id'])) {
            $this->loadData();
        }
        if ($this->data['forum'] && isset($this->data['forum']['forum_id'])) {
            return new fotostranaForum($this->data['forum']);
        }
    }

}

class fotostranaForum extends fotostranaObject
{

    function __construct($params)
    {
        if (!isset($params['forum_id'])) {
            throw new fotostranaError("Try to create fotostranaForum object without forum_id");
        }
        $this->data = $params;
    }

    function subforums()
    {
        /** @var $r fotostranaRequest */
        if ($this->data['forum_id']) {
            if (!isset($this->data['subforums'])) {
                $this->data['subforums'] = array();
                $r = $this->request();
                $r->setMethod('ForumCommunity.getSubforums');
                $r->setParam('communityId', $this->data['community_id']);
                $r->setParam('forumId', $this->data['forum_id']);
                $apiresult = $r->get();
                if ($apiresult && is_array($apiresult)) {
                    foreach ($apiresult['response'] as $subforum) {
                        $subforum['community_id'] = $this->data['community_id'];
                        $this->data['subforums'][] = new fotostranaSubforum($subforum);
                    }
                }
            }
            return $this->data['subforums'];
        }
    }

}

class fotostranaSubforum extends fotostranaObject
{

    function __construct($params)
    {
        if (!isset($params['subforum_id'])) {
            throw new fotostranaError("Try to create fotostranaSubforum object without subforum_id");
        }
        $this->data = $params;
    }

    function posts()
    {
        /** @var $r fotostranaRequest */
        if ($this->data['subforum_id']) {
            if (!isset($this->data['posts'])) {
                $this->data['posts'] = array();
                $r = $this->request();
                $r->setMethod('ForumCommunity.getPostsBySubforumId');
                $r->setParam('communityId', $this->data['community_id']);
                $r->setParam('subforumId', $this->data['subforum_id']);
                $r->setParam('perPage', 999999);
                $apiresult = $r->get();
                if (isset($apiresult['response']) && is_array($apiresult['response'])) {
                    foreach ($apiresult['response'] as $post) {
                        $this->data['posts'][] = new fotostranaSubforumPost($post);
                    }
                }
            }
            return $this->data['posts'];
        }
    }

}

class fotostranaSubforumPost extends fotostranaObject
{

    function __construct($params)
    {
        if (!isset($params['tlog_id'])) {
            throw new fotostranaError("Try to create fotostranaSubforum object without tlog_id");
        }
        $this->data = $params;
    }

    function __get($name) {
        switch ($name) {
            case 'id':
            case 'post_id':
                return $this->tlog_id;
                break;
            case 'title':
                return $this->tlog_name;
                break;
            case 'text':
                return $this->tlog_text;
                break;
            default:
                return parent::__get($name);
        }
    }

    function author()
    {
        return new fotostranaUser($this->author_id);
    }

}

?>