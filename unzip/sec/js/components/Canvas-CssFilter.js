/**
 * Canvas
 * Drawing/graphics functions
 */
Canvas.LABEL_ON_AXIS = null;
Canvas.LABEL_ON_OPPOSITE = 1;
Canvas.LABEL_ON_BOTH = 2;
/*
 * Constructor
 */
function Canvas(div) {
  this.div = div;
  this.graph = null;
  this.ie = navigator.userAgent.indexOf("MSIE") > -1;
  this._setDimensions();
}
Canvas.prototype = {
  div:null,    // canvas <div>
  graph:null,  // {'x':{'values':[from#,to#],pos:{'start':#,'end':#,'inc':#}},'y':...} 
  ie:null,     // true=IE browser
  /*
   * Define a graph within canvas
   * xRange, yRange: [#start,#end] values
   * dim: optional {'top':#,'left':#,'height':#,'width':#} relative to canvas
   */
  defineGraph:function(xRange, yRange, dim) {
    var pos = {};
    if (dim) {
      pos = dim;
    } else {
      pos = {
        'top':0,
        'left':0,
        'height':this.div.dim.height,
        'width':this.div.dim.width
        };
    }
    this.graph = {
      'pos':pos,
      'xRange':this._defineRange(xRange, pos.left, pos.width, 1),
      'yRange':this._defineRange(yRange, pos.top + pos.height, pos.height, -1)
      };
  },
  _defineRange:function(values, start, length, direction) {
    var range = {
      'values':values,
      'pos':{
        'start':start,
        'end':start + length,
        'inc':direction * length / (values[1] - values[0])
        }
      };
    return range; 
  },
  /*
   * Draw graph lines
   * - xAxis, yAxis: {'title':$,'lineEvery':#,'labelEvery':#, 'labelOn':#}   
   *                 lineEvery optional, default=1 (line on each value)
   *                 labelEvery optional, default=0 (no label)
   *                 labelOn optional, default LABEL_ON_AXIS 
   * - styleBox: optional {'color':$,'size':#}
   */
  drawGraph:function(xAxis, yAxis, styleBox) {
    xAxis.x = true;
    var xr = this.graph.xRange.values;
    var yr = this.graph.yRange.values;
    var colorLite = '#C1E1FF';
    var colorBold = '#6FBAFF';
    this.graph.margin = (styleBox) ? styleBox.size : 0;
    this._drawGraphLines(xAxis, this.graph.xRange, xAxis.lineEvery);
    this._drawGraphLines(yAxis, this.graph.yRange, yAxis.lineEvery);
    this._drawGraphLines(xAxis, this.graph.xRange, xAxis.labelEvery, true);
    this._drawGraphLines(yAxis, this.graph.yRange, yAxis.labelEvery, true);
    if (styleBox) {
      this.box([xr[0], yr[0]], [xr[1], yr[1]], styleBox);
    }
  },
  _drawGraphLines:function(axis, range, every, withLabel) {
    if (every == null && withLabel == null) {
      every = 1;
    }
    if (every) { 
      for (var v = range.values[0]; v <= range.values[1]; v = v + every) {
        var c1 = (axis.x) ? [v, this.graph.yRange.values[0]] : [this.graph.xRange.values[0], v];
        var c2 = (axis.x) ? [v, this.graph.yRange.values[1]] : [this.graph.xRange.values[1], v];
        if (withLabel) {
          this.line(c1, c2, {'color':Canvas.LINE_COLOR_BOLD, 'size':2});
          if (axis.labelOn == Canvas.LABEL_ON_OPPOSITE || axis.labelOn == Canvas.LABEL_ON_BOTH) {
            this._drawLabel(axis, v, c2, true);
          }
          if (axis.labelOn == Canvas.LABEL_ON_AXIS || axis.labelOn == Canvas.LABEL_ON_BOTH) {
            this._drawLabel(axis, v, c1);
          }
        } else {
          this.line(c1, c2, {'color':Canvas.LINE_COLOR_LITE});
        }
      }
    }
  },
  _drawLabel:function(axis, value, coords, onOpposite) {
    var lbl = document.createElement('div');
    lbl.className = 'cv-label';
    this.div.appendChild(lbl);
    lbl.innerText = value;
    var pt = this._toPt(coords);
    if (axis.x) {
      var width = this.graph.xRange.pos.inc * axis.labelEvery;
      lbl.style.width = width;
      lbl.style.textAlign = 'center';
      lbl.style.left = pt.x - width / 2;
      if (onOpposite) {
        lbl.style.top = pt.y - this.graph.margin - 13;       
      } else {
        lbl.style.top = pt.y + this.graph.margin;
      }
    } else {
      lbl.style.width = 100;
      lbl.style.top = pt.y - 6;
      if (onOpposite) {
        lbl.style.textAlign = 'left';
        lbl.style.left = pt.x + 2 + this.graph.margin;
      } else {
        lbl.style.textAlign = 'right';
        lbl.style.left = pt.x - 103 - this.graph.margin;
      } 
    }
  },
  /*
   * Draw a line 
   * - from, to: [x#,y#]
   * - style: optional {'color':$,'size':#}  // size: line thickness, default 1
   */
  line:function(from, to, style) {
    var pts = this._toPts(from, to);
    var pt1 = pts[0];
    var pt2 = pts[1];
    var line = document.createElement('div');
    this.div.appendChild(line);
    line.className = 'cv-line';
    style = denull(style, {});
    var size = denull(style.size, 1);
    var color = denull(style.color, 'black');
    var adj = size / 2;
    line.style.borderColor = color;
    line.style.borderWidth = size + 'px 0 0 0';
    var length = Math.sqrt((pt1.x - pt2.x) * (pt1.x - pt2.x) + (pt1.y - pt2.y) * (pt1.y - pt2.y));
    line.style.width = length + 'px';
    if (this.ie) {
      line.style.top = (pt2.y > pt1.y) ? (pt1.y - adj) + 'px' : (pt2.y - adj) + 'px';
      line.style.left = (pt1.x - adj) + 'px';
      var nCos = (pt2.x - pt1.x) / length;
      var nSin = (pt2.y - pt1.y) / length;
      line.style.filter = 'progid:DXImageTransform.Microsoft.Matrix(sizingMethod="auto expand", M11=' + nCos + ', M12=' + -1 * nSin + ', M21=' + nSin + ', M22=' + nCos + ')';
    } else {
      var angle = Math.atan((pt2.y - pt1.y) / (pt2.x - pt1.x));
      line.style.top = pt1.y + 0.5 * length * Math.sin(angle) + 'px';
      line.style.left = pt1.x - 0.5 * length * (1 - Math.cos(angle)) + 'px';
      line.style.MozTransform = line.style.WebkitTransform = line.style.OTransform = 'rotate(' + angle + 'rad)';
    }
  },
  /*
   * Draw a box around points
   * - from, to: [x#,y#]
   * - style: optional {'color':$,'size':#}  // size: border size, default 1
   */
   box:function(from, to, style) {
    var pts = this._toPts(from, to);
    var pt1 = pts[0];
    var pt2 = pts[1];
    var box = document.createElement('div');
    this.div.appendChild(box);
    box.className = 'cv-box';
    style = denull(style, {});
    var size = denull(style.size, 1);
    var color = denull(style.color, 'black');
    if (pt2.y > pt1.y) {
      box.style.top = (pt1.y - size - 1) + 'px';
      box.style.height = (pt2.y - pt1.y + 1) + 'px';
    } else {
      box.style.top = (pt2.y - size - 1) + 'px';
      box.style.height = (pt1.y - pt2.y + 1) + 'px';      
    }
    box.style.left = (pt1.x - size - 1) + 'px';
    box.style.width = (pt2.x - pt1.x + 1) + 'px';
    box.style.borderColor = color;
    box.style.borderWidth = size + 'px'; 
  },
  /*
   * Plot a point
   * - coords: [x#,y#]
   * - style: optional {'color':$,'size':#}  // size: side length, default 3
   */
  plot:function(coords, style) {
    var pt = this._toPt(coords);
    var size = denull(style.size, 3);
    var color = denull(style.color, 'black');
    var adj = size / 2;
    var div = document.createElement('div');
    this.div.appendChild(div);
    div.className = 'cv-pt';
    style = denull(style, {});
    div.style.backgroundColor = color;
    div.style.height = size + 'px';
    div.style.width = size + 'px';
    div.style.top = pt.y - adj;
    div.style.left = pt. x - adj; 
  },
  /*
   * Plot a set of points with lines between
   * - coordSet: [[x#,y#],..]
   * - styleLine, stylePt: optional {'color':$,'size':#}
   */
  plotLines:function(coordSet, styleLine, stylePt) {
    for (var i = 0, j = coordSet.length - 1; i < j; i++) {
      var from = coordSet[i];
      var to = coordSet[i + 1];
      this.line(from, to, styleLine);
    }
    for (var i = 0, j = coordSet.length; i < j; i++) {
      this.plot(coordSet[i], stylePt);
    }
  },
  //
  _toPts:function(from, to) {
    var pf = this._toPt(from);
    var pt = this._toPt(to);
    if (pf.x < pt.x) {
      return [pf, pt];
    } else {
      return [pt, pf];
    }
  },
  _toPt:function(coords) {
    var x = coords[0];
    var y = coords[1];
    if (this.graph) {
      x = this._adjustPos(coords[0], this.graph.xRange, 'x');
      y = this._adjustPos(coords[1], this.graph.yRange, 'y');
    }
    return {
      'x':x + this.div.dim.left,
      'y':y + this.div.dim.top
      };
  },
  _adjustPos:function(value, range, label) {
    if (value < range.values[0] || value > range.values[1]) {
      throw new Error('Value ' + label + '=' + value + ' out of range.');
    }
    var pos = range.pos.start + (value - range.values[0]) * range.pos.inc;
    return pos; 
  },
  _setDimensions:function() {
    var dim = Canvas.getPos(this.div);
    dim.height = this.div.offsetHeight;
    dim.width = this.div.offsetWidth;
    this.div.dim = dim;
  }
};
/**
 * Statics
 */
Canvas.LINE_COLOR_LITE = '#C1E1FF';
Canvas.LINE_COLOR_BOLD = '#87C5FF';
 /*
  * Get absolute position of element
  * Returns {'left':#,'top':#}; 
  */
Canvas.getPos = function(e) {
  var cl = 0;
  var ct = 0;
  if (e.offsetParent) {
    do {
      cl += e.offsetLeft;
      ct += e.offsetTop;
    } while (e = e.offsetParent);
  }
  return {
    'left':cl,
    'top':ct
    };
};
