/**
 * Facesheet
 */
Facesheet = Object.Rec.extend({
  //
  onstale:function() {},
  //
  onload:function() {
    this.cid = this.client && this.client.clientId;
    this.procedures = Procedures.revive(this.procedures);
    this.surgs = this.procedures.getSurgs();
    this.docstubs = DocStubs.revive(this.docstubs);
    this.diagnoses = Diagnoses.revive(this.diagnoses);
    this.meds = Meds.revive(this.meds);
    this.allergies = Allergies.revive(this.allergies);
    this.tracking = TrackItems.revive(this.tracking);
    if (window.IpcHms) 
      this.hms = IpcHms.from(this);
    if (window.PortalUser) 
      this.portalUser = PortalUser.from(this);
    if (window.Immuns) {
      this.immuns = Immuns.revive(this.immuns);
      this.immunCd = ImmunCd.revive(this.immunCd);
    }
  },
  refreshNewCrop:function(f) {
    this.cuTimestamp = f.cuTimestamp;
    this.allergies = Allergies.revive(f.allergies);
    this.meds = Meds.revive(f.meds);
    this.activeMeds = f.activeMeds;
    this.activeAllers = f.activeAllers;
    return this;
  },
  refreshClient:function(f) {
    this.cuTimestamp = f.cuTimestamp;
    this.client = f.client;
    return this;
  },
  setImage:function(img) {
    this.cid.img = img;
  },
  //
  polling:function() {
    var self = this;
    var p = Polling.StaleFacesheet;
    return {
      start:function() {
        if (p.isLoaded())
          p.resume();
        else
          p.load(self.onstale).start(self);
      },
      stop:function() {
        p.stop();
      }
    }
  },
  ajax:function(worker) {
    worker = worker || Html.Window;
    var self = this;
    return {
      /*
       * @arg int cid
       * @arg fn(Facesheet) callback
       */
      fetch:function(cid, callback) {
        Ajax.Facesheet.get(cid, worker, callback);
      },
      /*
       * @arg fn(Facesheet) callback only if facesheet updated
       */
      refetchIfChanged:function(callback) {
        Polling.StaleFacesheet.stop();
        Ajax.Facesheet.getIfUpdated(self.cid, self.cuTimestamp, worker, function(fs) {
          if (fs) 
            callback(fs);
          else 
            Polling.StaleFacesheet.start(self);
        })
      },
      refetch:function(callback) {
        Ajax.Facesheet.get(self.cid, worker, callback);
      },
      refetchProcs:function(callback) {
        Ajax.Procedures.getAll(self.cid, worker, function(recs) {
          self.procedures = recs;
          self.surgs = self.procedures.getSurgs();
          callback(recs);
        })
      },
      refetchHms:function(callback) {
        Ajax.Ipc.getIpcHmsFor(IpcHms.cid, worker, function(recs) {
          Ajax.Ipc.getIpcHmsFor(IpcHms.cid, worker, callback);
          self.hms = recs;
          callback(recs);
        })
      }
    }
  }
})
/**
 * DocStub
 */
DocStub = Object.Rec.extend({
  /*
   type
   id
   cid
   date
   timestamp
   name
   desc
   signed  // '04-May-2012 3:55PM by Dr. Clicktate'
   */
  onload:function() {
    if (this.Unreviewed) 
      this.Client = this.Unreviewed.Client;
  },
  equals:function(that) {
    return this.id == that.id && this.type == that.type;
  },
  getKey:function() {
    return DocStubKey.from(this);
  },
  isSession:function() {
    return this.type == C_DocStub.TYPE_SESSION;
  },
  //
  ajax:function(worker) {
    worker = worker || Html.Window;
    var self = this;
    return {
      /*
       * @arg fn(DocStubPreview) callback
       */
      preview:function(callback) {
        Ajax.Facesheet.Documentation.preview(self, worker, callback);
      },
      /*
       * @arg fn(DocStub) callback
       */
      refetch:function(callback) {
        Ajax.Facesheet.Documentation.refetch(self, worker, function(rec) {
          if (self.Client)
            rec.Client = self.Client;
          callback(rec);
        })
      }
    }
  }
})
DocStubKey = {
  create:function(type, id) {
    return type + ',' + id;
  },
  from:function(rec) {
    if (rec && rec.type && rec.id)
      return this.create(rec.type, rec.id);
  },
  asProc:function(id) {
    return this.create(C_DocStub.TYPE_RESULT, id);
  },
  asSession:function(id) {
    return this.create(C_DocStub.TYPE_SESSION, id);
  }
}
//
DocStubs = Object.RecArray.of(DocStub, {
  get:function(key) {
    if (this._map == null)
      this.createMap();
    return this._map[key];
  },
  get_proc:function(id) {
    return this.get(DocStubKey.asProc(id));
  },
  get_session:function(id) {
    return this.get(DocStubKey.asSession(id));
  },
  getOpenSessions:function() {
    if (this._os == null) 
      this._os = Array.filter(this, function(rec) {
        return (rec.isSession() && rec.signed == null) ? rec : null;
      })
    return this._os;
  },
  //
  ajax:function(worker) {
    var self = this;
    return {
      fetch:function(cid, callback) {
        Ajax.Facesheet.Documentation.getAll(cid, callback);
      },
      fetchUnreviewed:function(callback) {
        Ajax.Facesheet.Documentation.getUnreviewed(worker, callback);
      }
    }
  },
  createMap:function() {
    this._map = Map.from(this, function(stub) {
      return DocStubKey.from(stub);
    })
  }
})
//
DocStubPreview = DocStub.extend({
  onload:function() {
    DocStub.onload.call(this);
    this.setr('Preview', PreviewRec);
    this.setr('ReviewThread', ThreadStub);
  },
  resetPreview:function() {
    this.Preview = null;
  },
  needsReview:function() {
    return this.ReviewThread && this.ReviewThread.isUnreviewed();
  },
  getReviewedLabel:function() {
    return this.ReviewThread && this.ReviewThread.getReviewedLabel();
  },
  ajax:function(worker) {
    var self = this;
    worker = worker || Html.Window;
    return {
      reviewed:function(callback) {
        Ajax.Facesheet.Documentation.reviewed(self.ReviewThread, worker, callback);
      },
      forward:function(userId, callback) {
        Html.Window.working(true);
        Ajax.Facesheet.Documentation.forward(self.type, self.id, userId, worker, callback);
      },
      //
      preview:function(callback) {
        DocStub.ajax.call(self, worker).preview(callback);
      },
      refetch:function(callback) {
        DocStub.ajax.call(self, worker).refetch(callback);
      }
    }
  }  
})
PreviewRec = Object.Rec.extend({
  onload:function() {
    this.setr('ScanIndex', ScanIndex);
    this.setr('ScanFiles', ScanFiles);
    if (this.MsgPosts) {
      this.MsgPosts.each(function(post) {
        if (post.Stub)
          post.Stub = DocStubPreview.revive(post.Stub);
      })
    }
  }
})

/** 
 * ThreadStub
 */
ThreadStub = Object.Rec.extend({
  onload:function() {
    this.Inbox = InboxStub.revive(this.Inbox);
    this.Posts = PostStubs.revive(this.Posts);
  },
  isUnreviewed:function() {
    return this.Inbox && this.Inbox.isUnreviewed();
  },
  isClosed:function() {
    return this.status == C_MsgThread.STATUS_CLOSED;
  },
  getReviewedLabel:function() {
    if (this.isClosed() && this.Posts && this.Posts.reviewedPost) 
      return 'Reviewed ' + this.Posts.reviewedPost.dateCreated + ' by ' + this.Posts.reviewedPost.author;
  }
})
ThreadStubs = Object.RecArray.of(ThreadStub, {
  //
})
InboxStub = Object.Rec.extend({
  isUnreviewed:function() {
    return this.isRead == C_MsgInbox.IS_UNREVIEWED;
  }
})
PostStub = Object.Rec.extend({
})
PostStubs = Object.RecArray.of(PostStub, {
  onload:function() {
    var post = this.end();
    if (post.action == C_MsgPost.ACTION_REVIEWED)
      this.reviewedPost = post;
  }
})
/**
 * Diagnosis
 */
Diagnosis = Object.Rec.extend({
  onload:function() {
    this._active = (this.active) ? 'Active Only' : 'Inactive Only';
  },
  //
  uiDateRange:function() {
    var s = this.date;
    if (this.dateClosed)
      s += ' - ' + this.dateClosed;
    return s;
  },
  uiRecon:function() {
    var s = '';
    if (this.dateRecon && this.reconBy) {
      s = this.dateRecon;
      if (C_Users[this.reconBy]) {
        s += ' ' + C_Users[this.reconBy];
      }
    }
    return s;
  },
  cloneNewActive:function() {
    var rec = Diagnosis.asNew(this.clientId);
    rec.text = this.text;
    rec.icd = this.icd;
    return rec;
  },
  //
  asNew:function(cid) {
    return Diagnosis.revive({
      clientId:cid,
      date:DateUi.getToday(),
      status:C_Diagnosis.STATUS_ACTIVE});
  }
})
//
Diagnoses = Object.RecArray.of(Diagnosis, {
  //
  ajax:function(worker) {
    worker = worker || Html.Window;
    return {
      setNone:function(cid, callback) {
        Ajax.Facesheet.Diagnoses.setNone(cid, worker, callback);
      },
      reconcile:function(cid, callback) {
        Ajax.Facesheet.Diagnoses.reconcile(cid, worker, callback);
      }
    }
  },
  actives:function() {
    return Diagnoses.revive(Array.filterOn(this, 'active'));
  }
})
/**
 * Med
 */
Med = Object.Rec.extend({
  onload:function() {
    this._active = (this.active) ? 'Active Only' : 'Inactive Only';
    this._status = (this.active) ? 'Active' : 'Discontinued';
  }
})
//
Meds = Object.RecArray.of(Med, {
  //
})
/**
 * Allergy
 */
Allergy = Object.Rec.extend({
  onload:function() {
    this._active = (this.active) ? 'Active Only' : 'Inactive Only';
  }
})
//
Allergies = Object.RecArray.of(Allergy, {
  //
})
/**
 * Track Item
 */
TrackItem = Object.Rec.extend({
  onload:function() {
    this.setr('DocSession', DocStub);
    this.setr('DocProc', DocStub);
    this.setr('DocScan', DocStub);
    this.setr('Provider_schedWith', Provider);
    this.setr('Address_schedLoc', Facility);
    if (this._schedDate == null && this.selfRefer) {
      this._schedDate = '(Self-Ref)';
      this.schedDate = '(Self-Ref)';
      this._overdue = '';
    }
  },
  //
  ajax:function() {
    var self = this;
    return {
      fetch:function(callback) {  // fetch rest of props for editing
        Ajax.Tracking.get(self.trackItemId, callback);
      }
    }
  }
})
//
TrackItems = Object.RecArray.of(TrackItem, {
  //
  ajax:function(worker) {
    return {
      fetch:function(type, criteria, callback) {
        Ajax.Tracking.getTrackItems(type, criteria, worker, callback);
      },
      fetch_open:function(cid, callback) {
        this.fetch(0, {'cid':cid}, callback);
      },
      fetch_unsched:function(cid, callback) {
        this.fetch(1, {'cid':cid}, callback);
      },
      fetch_sched:function(cid, callback) {
        this.fetch(2, {'cid':cid}, callback);
      }
    }
  }
})
//
ImmunEntry = Object.Rec.extend({
  /*
   par
   lots 
     fullname
     lot
     dateExp
   */
  onload:function() {
    this.setr('par', Par);
    if (this.lots == null)
      this.lots = {};
  },
  getLot:function(key) {
    return this.lots[key];
  },
  getLotKey:function(rec) {
    if (rec.name)
      return rec.name + '|' + String.trim(rec.tradeName);
  },
  saveLot:function(rec) {
    var key = this.getLotKey(rec);
    var lot = {'lot':rec.lot,'dateExp':rec.dateExp};
    this.lots[key] = lot;
  }
})
//
ClientDoc = Object.Rec.extend({
  /*
  clientDocId
  clientId
  type
  dateCreated
  createdBy
  html
  */
  //
  ajax:function(worker) {
    var self = this;
    var SVR = 'PatientDocs';
    return {
      createReferralCard:function(cid, html, callback/*ClientDoc*/) {
        if (callback == null) 
          Html.ServerForm.submit(SVR, 'createReferralCard', {'cid':cid,'html':html,'print':1});
        else
          Ajax.postr(SVR, 'createReferralCard', {'cid':cid,'html':html}, ClientDoc, worker, callback);
      },
      download:function() {
        Html.ServerForm.submit(SVR, 'print', self.clientDocId);
      },
      sendToPortal:function() {
        var stub = {
          'type':C_DocStub.TYPE_CLIENTDOC,
          'id':self.clientDocId,
          'name':'Referral Appointment'};
        Page.Nav.goMessageNewPortal(self.clientId, stub);
      }
    }
  }
})
