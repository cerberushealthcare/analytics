Html.Canvas = {
  create:function(container, height, width) {
    var wrapper = Html.Tile.create(container, 'CanvasWrapper').setDim(height, width);
    var self = Html.Tile.create(wrapper, 'Canvas');
    var canvas;
    if (document.implementation.hasFeature("http://www.w3.org/TR/SVG11/feature#BasicStructure", "1.1"))
      canvas = new Canvas_SVG(self); 
    else
      canvas = new Canvas(self);
    return self.aug({
      //
      load:function(trends) {
        self.trends = Trends.from(trends);
        self.draw();
      },
      draw:function() {
        self.defineGraph(self.trends);
        self.drawGraph(self.trends);
        self.drawTrends(self.trends);
      },
      drawTrends:function(trends) {
        trends.each(function(trend) {
          self.drawTrend(trend);
        })
      },
      drawTrend:function(trend) {
        canvas.plotLines(trend.getCanvasPts(), {'color':trend.color, size:2});
      },
      drawGraph:function(trends) {
        var xaxis = trends.getCanvasAxis_x();
        var yaxis = trends.getCanvasAxis_y();
        canvas.drawGraph(xaxis, yaxis, {'color':'black'});
      },
      defineGraph:function(trends) {
        var dim = {
          'top':10, 
          'left':50, 
          'height':height - 30, 
          'width':width - 60};
        canvas.defineGraph(
          trends.getCanvasGraph_x(), 
          trends.getCanvasGraph_y(), 
          dim);
      }
    })
  }
}
Trends = {
  from:function(e) {
    return Array.arrayify(e).aug(this);
  },
  //
  getCanvasGraph_x:function() {
    return this.getCanvasGraph('x');
  },
  getCanvasGraph_y:function() {
    return this.getCanvasGraph('y');
  },
  getCanvasGraph:function(xy) {
    var range = this.getCombinedRange(xy);
    var label = this.length && this[0].pts[xy + 'label'];
    return {
      'values':range.limits,
      'label':label};
  },
  getCombinedRange:function(xy) {
    var fid = xy + 'range';
    if (this[fid]) 
      return this[fid];
    var ranges = Array.from(this, fid);
    var range = Trend.Range.combine(ranges).withPad();
    return this[fid] = range; 
  },
  getCanvasAxis_x:function() {
    return this.getCanvasAxis('x', 2);
  },
  getCanvasAxis_y:function() {
    return this.getCanvasAxis('y', 1);
  },
  getCanvasAxis:function(xy, labelMult) {
    var range = this.getCombinedRange(xy);
    var span = range.getSpan();
    var fn = range.julian ? dateFromJd : null;
    var every = Math.round(span / 10);
    return {
      'lineEvery':every,
      'labelEvery':every * labelMult,
      'labelSkip':every,
      'labelFn':fn};
  }
}
Trend = {
  /*
   Pts[] pts
   Range xrange
   Range yrange
   */
  create:function(label, color) {
    return Object.create(this)
      .set('label', label)
      .set('color', color || 'blue');
  },
  load:function(pts) {
    this.pts = pts;
    this.setRange('x');
    this.setRange('y');
    return this;
  },
  setRange:function(xy) {
    var fid = xy + 'range';
    var vals = this.pts['getValues_' + xy]();
    var julian = vals.length && vals[0].julian;
    var values = Array.from(vals, 'value');
    this[fid] = Trend.Range.from(values, julian);
  },
  getCanvasPts:function() {
    return this.pts.getCanvasPts();
  }
}
Trend.Range = {
  /*
   int[] range [from,to]
   bool julian 
   */
  from:function(values, julian) {
    return Object.create(this)
      .set('limits', [Math.smallest(values), Math.largest(values)])
      .set('julian', julian);
  },
  combine:function(ranges) {
    if (ranges.length == 1)
      return ranges[0];
    var values = [], julian;
    ranges.each(function(range) {
      values.push(range.limits[0]);
      values.push(range.limits[1]);
      julian = range.julian;
    })
    return Trend.Range.from(values, julian);
  },
  //
  withPad:function(pad) {
    var min = this.limits[0];
    var max = this.limits[1];
    var offset = (max - min) / (pad || 8);
    min = this.round(min - offset, -1);
    max = this.round(max + offset, 1);
    this.limits = [min, max];
    return this;
  },
  round:function(i, inc) {
    if (this.julian)
      return Math.round(i);
    else
      return Math.round((i + 10 * inc) / 10) * 10;
  },
  getSpan:function() {
    return this.limits[1] - this.limits[0];
  }
}
Trend.Pts = {
  /*
   xlabel
   ylabel
   */
  create:function(array, xlabel, ylabel) {
    array = array || [];
    array.xlabel = xlabel;
    array.ylabel = ylabel;
    return array.aug(this);
  },
  from:function(recs, xlabel, ylabel, getter) {
    var array = Array.from(recs, getter || 'asPt');
    return Trend.Pts.create(array, xlabel, ylabel);
  },
  from_asDateValue:function(recs, getter) {
    return Trend.Pts.from(recs, 'Date', 'Value', getter);
  },
  //
  getValues:function(fid) {
    return Array.from(this, fid);
  },
  getValues_x:function() {
    return this.getValues('x');
  },
  getValues_y:function() {
    return this.getValues('y');
  },
  getCanvasPts:function() {
    return Array.from(this, 'getCanvasPt');
  }
}
Trend.Pt = {
  /*
   Val x
   Val y
   */
  create:function(xv, yv) {
    return Object.create(this)
      .set('x', xv)
      .set('y', yv);
  },
  fromDateValue:function(date, value) {
    return Trend.Pt.create(Trend.Val_Julian.from(date), Trend.Val.from(value));
  },
  getCanvasPt:function() {
    return [this.x.value, this.y.value];
  }
}
Trend.Val = {
  /*
   label
   value
   */
  from:function(e) {
    return Object.create(this)
      .setLabel(e)
      .setValue(e);
  },
  setLabel:function(e) {
    this.label = e;
    return this;
  },
  setValue:function(e) {
    this.value = String.toFloat(e);
    return this;
  }
}
Trend.Val_Julian = Object.extend(Trend.Val, {
  julian:1,
  setValue:function(e) {
    this.value = jdFromDate(e);
    return this;
  }
})
