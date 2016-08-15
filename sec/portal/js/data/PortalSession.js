/**
 * Rec PortalSession
 */
PortalSession = Object.Rec.extend({
  needsChallenge:function() {
    return this.status == C_PortalUser.STATUS_RESET;
  },
  needsPassword:function() {
    return this.status == C_PortalUser.STATUS_CHALLENGED;
  },
  needsTos:function() {
    return this.User && this.User._tos;
  }
})