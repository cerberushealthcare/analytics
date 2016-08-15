/**
 * Client UI Library
 */
var ClientUi = {
  formatName:function(c) {
    return trim(c.lastName + ', ' + c.firstName + ' ' + denull(c.middleName));
  },
  isMale:function(c) {
    return c.sex == 'M';
  },
  formatSex:function(c) {
    return (this.isMale(c)) ? 'male' : 'female';
  },
  /*
   * Create patient <A> linking to facesheet
   * - c: ClientStub
   * - args: optional, extra qs args (client ID supplied by default)
   * - page: optional, to override facesheet default 
   */
  createClientAnchor:function(c, args, page) {
    var cls = (ClientUi.isMale(c)) ? 'action umale' : 'action ufemale';
    page = denull(page, Page.PAGE_FACESHEET);
    args = denull(args, {});
    args.id = c.clientId;
    return createAnchor(null, Page.url(Page.PAGE_FACESHEET, args), cls, ClientUi.formatName(c));
  }
}
