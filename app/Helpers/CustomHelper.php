<?php

use App\Models\Comment;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cookie;
use App\Models\User;

if (!function_exists('getLoginUser')) {
    function getLoginUser()
    {
        if (Cookie::get('sessionId')) {
            $sessionIdFromCookie = Cookie::get('sessionId');
            Session::setId($sessionIdFromCookie);
            $userId = Session::get('loginId');
        } else {
            $userId = Session::get('loginId');
        }
        $user = User::find($userId);
        return $user;
    }
    function hasChild($data, $id)
    {
        foreach ($data as $childItem) {
            if ($childItem->parent_comment_id == $id) {
                return true;
            }
        }
        return false;
    }

    function commentDataTree($data, $parentId = 0, $level = 0)
    {
        $result = array();
        foreach ($data as $item) {
            if ($item->parent_comment_id == $parentId) {
                // $parentComment = Comment
                $user = User::find($item->id);
                if ($user) {
                    $item->userComment = $user->username;
                } else {
                    $item->user = null;
                }
                $item->level = $level;
                $result[] = $item;
                if (hasChild($data, $item->id)) {
                    $resultChild = commentDataTree($data, $item->id, $level + 1);
                    $result = array_merge($result, $resultChild);
                }
            }
        }
        return $result;
    }

    function getNestedRepliesWithUser($comments)
    {
        $nestedReplies = collect();

        foreach ($comments as $comment) {
            if ($comment->comments->isNotEmpty()) {
                $nestedReplies = $nestedReplies->merge($comment->comments);
                $nestedReplies = $nestedReplies->merge(getNestedRepliesWithUser($comment->comments));
            }
            // Lấy thông tin user của comment reply

            // Lấy thông tin user từ parent comment
            $parentCommentId = $comment->parent_comment_id;
            if ($parentCommentId) {
                $parentComment = Comment::find($parentCommentId);
                if ($parentComment) {
                    $userOfParentComment = $parentComment->user;
                    if ($userOfParentComment) {
                        $comment->parentUser = $userOfParentComment;
                    }
                }
            }

            $userOfReply = $comment->user;
            if ($userOfReply) {
                $comment->user = $userOfReply;
            }
        }

        return $nestedReplies;
    }
}
