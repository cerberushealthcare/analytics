/*/**
 * Rec MsgThread
 */
MsgThread = Object.Rec.extend({
  //
  onload:function() {
    this.setr('MsgPosts', MsgPosts);
  },
  isRead:function() {
    return this.Inbox.isRead == C_MsgInbox.IS_READ;
  },
  getLastPost:function() {
    if (this.MsgPosts)
      return this.MsgPosts.end();
  },
  getReplyId:function() {
    if (this.MsgPosts) {
      for (var i = this.MsgPosts.length; i > 0; i--) {
        var post = this.MsgPosts[i - 1];
        if (post.isAuthorOffice())
          return post.authorId;
      }
    }
  }
})
MsgThreads = Object.RecArray.of(MsgThread, {
  //
})
MsgPost = Object.Rec.extend({
  //
  isAuthorOffice:function() {
    return this.authorType == C_MsgPost.AUTHOR_TYPE_OFFICE;
  }
})
MsgPosts = Object.RecArray.of(MsgPost, {
  //
})
