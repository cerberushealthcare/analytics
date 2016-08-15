/**
 * Canvas_SVG
 * Drawing/graphics functions using SVG
 */
Canvas_SVG.LABEL_ON_AXIS = null;
Canvas_SVG.LABEL_ON_OPPOSITE = 1;
Canvas_SVG.LABEL_ON_BOTH = 2;
//
Canvas_SVG.QUAD_UL = 0;
Canvas_SVG.QUAD_UR = 1;
Canvas_SVG.QUAD_LR = 2;
Canvas_SVG.QUAD_LL = 3;
//
function Canvas_SVG(div) {
  this.div = div;
  this.graph = null;
  this._prepareCanvas();
}
//
Canvas_SVG.prototype = {
  div:null,    // canvas <div>
  graph:null,  // {'x':{'values':[from#,to#],pos:{'start':#,'end':#,'inc':#}},'y':...} 
  ie:null,     // true=IE browser
  //
  clear:function() {
    clearChildren(this.div);
    this.svg = this._createSvg('svg');
    this.div.appendChild(this.svg);
    this.img = null;
  },
  _createSvg:function(object) {
    return document.createElementNS("http://www.w3.org/2000/svg", object);
  },
  _appendSvg:function(object) {
    var shape = this._createSvg(object);
    this.svg.appendChild(shape);
    return shape;
  },
  _createPolyline:function(pts, strokecolor, fillcolor, strokeweight) {
    var x = pts[0].x;
    var y = pts[0].y + 1; 
    var w = pts[1].x - x;
    var h = pts[1].y - y + 1;
    var points = [x, y, x, y + h, x + w, y + h, x + w, y, x, y, x, y + h];
    var line = this._appendSvg('polyline');
    line.setAttribute("stroke-width", 1);
    line.setAttribute("stroke", strokecolor);
    line.strokecolor = strokecolor;
    if (fillcolor) 
      line.setAttribute('fill', fillcolor);
    else
      line.setAttribute('fill', 'none');
    line.setAttribute('points', points.join(','));
    return line;
  },
  _addTitle:function(polyline, title) {
    if (title) {
      var t = this._createSvg('title');
      t.textContent = title;
      polyline.style.cursor = 'pointer';
      polyline.appendChild(t);
    }
  },
  _createLine:function(pts, strokecolor, strokeweight) {
    var line = this._appendSvg('line');
    line.setAttribute("stroke-width",  strokeweight);
    line.setAttribute("stroke", strokecolor);
    line.setAttribute("x1", pts[0].x);
    line.setAttribute("x2", pts[1].x);
    line.setAttribute("y1",  pts[0].y + 1);
    line.setAttribute("y2",  pts[1].y + 1);
    return line;
  },
  /*
   * Define a graph within canvas
   * - xRange, yRange: {'values':[#start,#end],'label':$}
   * - dim: optional {'top':#,'left':#,'height':#,'width':#,'src':$} 
   *        top, left, height, width: relative to canvas
   *        src: optional background graph image source
   */
  defineGraph:function(xRange, yRange, dim) {
    this.clear();
    if (dim.img) {
      var img = document.createElement('img');
      img.className = 'cv-graph';
      img.src = dim.img;
      img.style.left = 0; 
      img.style.top = 0;
      this.div.appendChild(img);
    }
    var pos = {};
    if (dim) {
      pos = dim;
      var h = dim.height + dim.top;
      var w = dim.width + dim.left;
      this.svg.setAttribute('width', w + 3);
      this.svg.setAttribute('height', h + 3);
      this.div.style.height = px(h + 30);
      this.div.style.width = px(w + 30);
    } else {
      pos = {
        'top':0,
        'left':0,
        'height':this.div.dim.height,
        'width':this.div.dim.width 
        };
    }
    var xRange = (xRange.values[0] < xRange.values[1]) ?  
      this._defineRange(xRange.values, xRange.label, pos.left, pos.width, 1) :
      this._defineRange([xRange.values[1], xRange.values[0]], xRange.label, pos.left + pos.width, pos.width, -1);
    var yRange = (yRange.values[0] < yRange.values[1]) ? 
      this._defineRange(yRange.values, yRange.label, pos.top + pos.height, pos.height, -1) :
      this._defineRange([yRange.values[1], yRange.values[0]], yRange.label, pos.top, pos.height, 1);
    this.graph = {
      'pos':pos,
      'xRange':xRange,
      'yRange':yRange
      };
  },
  _defineRange:function(values, label, start, length, direction) {
    var range = {
      'values':values,
      'label':label,
      'pos':{
        'start':start,
        'end':start + length,
        'dir':direction,
        'inc':direction * length / (values[1] - values[0])
        }
      };
    return range; 
  },
  /*
   * Draw graph lines
   * - xAxis, yAxis: {'title':$,'lineEvery':#,'labelEvery':#,'labelStart':#,'labelOn':#,labelFn:fn}   
   *                 lineEvery optional, default=1 (line on each value)
   *                 labelEvery optional, default=0 (no label)
   *                 labelSkip optional value to skip before labeling, default=0 (start with first)
   *                 labelOn optional, default=LABEL_ON_AXIS 
   *                 labelFn optional, function to format value
   * - styleBox: optional {'color':$,'size':#}
   */
  drawGraph:function(xAxis, yAxis, styleBox) {
    xAxis.x = true;
    var xr = this.graph.xRange.values;
    var yr = this.graph.yRange.values;
    this.graph.margin = (styleBox) ? denull(styleBox.size, 0) : 0;
    this._drawGraphLines(xAxis, this.graph.xRange, denull(xAxis.lineEvery, 1));
    this._drawGraphLines(yAxis, this.graph.yRange, denull(yAxis.lineEvery, 1));
    this._drawGraphLines(xAxis, this.graph.xRange, xAxis.labelEvery, true);
    this._drawGraphLines(yAxis, this.graph.yRange, yAxis.labelEvery, true);
    if (styleBox) {
      this.box([xr[0], yr[0]], [xr[1], yr[1]], styleBox);
    }
    this.graph.xRange.labelFn = xAxis.labelFn;
    this.graph.yRange.labelFn = yAxis.labelFn;
  },
  _drawGraphLines:function(axis, range, every, withLabel) {
    if (every) {
      var start = (withLabel && axis.labelSkip) ? range.values[0] + axis.labelSkip : range.values[0];
      for (var v = start; v <= range.values[1]; v = v + every) {
        var c1 = (axis.x) ? [v, this.graph.yRange.values[0]] : [this.graph.xRange.values[0], v];
        var c2 = (axis.x) ? [v, this.graph.yRange.values[1]] : [this.graph.xRange.values[1], v];
        if (withLabel) {
          if (axis.lineEvery) {
            this.line(c1, c2, {'color':Canvas_SVG.LINE_COLOR_BOLD, 'size':1.5});
          }
          if (axis.labelOn == Canvas_SVG.LABEL_ON_OPPOSITE || axis.labelOn == Canvas_SVG.LABEL_ON_BOTH) {
            this._drawLabel(axis, v, c2, true);
          }
          if (axis.labelOn == Canvas_SVG.LABEL_ON_AXIS || axis.labelOn == Canvas_SVG.LABEL_ON_BOTH) {
            this._drawLabel(axis, v, c1);
          }
        } else {
          if (axis.lineEvery) {
            this.line(c1, c2, {'color':Canvas_SVG.LINE_COLOR_LITE});
          }
        }
      }
    }
  },
  _drawLabel:function(axis, value, coords, onOpposite) {
    var lbl = document.createElement('div');
    lbl.className = 'cv-label';
    this.div.appendChild(lbl);
    lbl.innerText = (axis.labelFn) ? axis.labelFn(value) : value;
    var pt = new Pt(coords, this.graph);
    if (axis.x) {
      var width = Math.abs(this.graph.xRange.pos.inc * axis.labelEvery);
      lbl.style.width = width;
      lbl.style.textAlign = 'center';
      lbl.style.left = px(pt.x - width / 2 + 1);
      if (this.graph.yRange.pos.dir == 1) {
        onOpposite = ! onOpposite;
      }
      if (onOpposite) {
       lbl.style.top = px(pt.y - this.graph.margin - 12);        
      } else {
       lbl.style.top = px(pt.y + this.graph.margin + 1);
      }
    } else {
      lbl.style.width = 100;
      lbl.style.top = px(pt.y - 6);
      if (this.graph.xRange.pos.dir == -1) {
        onOpposite = ! onOpposite;
      }
      if (onOpposite) {
        lbl.style.textAlign = 'left';
        lbl.style.left = px(pt.x + 2 + this.graph.margin);
      } else {
        lbl.style.textAlign = 'right';
        lbl.style.left = px(pt.x - 102 - this.graph.margin);
      } 
    }
  },
  /*
   * Create an empty legend <div> over graph 
   * - pos: position of legend {'quadrant':#,'width':#,'height':#,'margin':#}
   *        quadrant: Canvas_SVG.QUAD_ (0=upperL, 1=upperR, 2=lowerR, 3=lowerL)
   * - innerText: optional
   */
  legend:function(pos, innerHtml) {
    pos = (pos) ? pos : {};
    pos.width = denull(pos.width, 0);
    pos.height = denull(pos.height, 0);
    pos.quadrant = denull(pos.quadrant, Canvas_SVG.QUAD_LR);
    pos.margin = denull(pos.margin, 0);
    switch (pos.quadrant) {
      case Canvas_SVG.QUAD_LR:
        pos.pt = new Pt([this.graph.xRange.values[1], this.graph.yRange.values[0]], this.graph);
        pos.left = pos.pt.x - pos.width;
        pos.top = pos.pt.y - pos.height;
        break;
      case Canvas_SVG.QUAD_UL:
        pos.pt = new Pt([this.graph.xRange.values[0], this.graph.yRange.values[1]], this.graph);
        pos.left = pos.pt.x;
        pos.top = pos.pt.y;
        pos.margin = -pos.margin;
        break;
    }
    var legend = document.createElement('div');
    this.div.appendChild(legend);
    legend.className = 'cv-legend';
    legend.style.height = pos.height;
    legend.style.width = pos.width;
    legend.style.left = px(pos.left - 10 - pos.margin);
    legend.style.top = px(pos.top - 10 - pos.margin);
    if (innerHtml) 
      legend.innerHTML = innerHtml;
    return legend;
  },
  /*
   * Plot a point
   * - coords: [x#,y#]
   * - style: optional {'color':$,'size':#}  // size: side length, default 30
   */
  plot:function(coords, style) {
    style = (style) ? style : {};
    var size = denull(style.size, 3);
    var color = denull(style.color, 'black');
    var from = new Pt(coords, this.graph);
    var to = new Pt(coords, this.graph);
    var title = from.toTitle(this.graph);
    var pts = [
      from.shift(-size, -size),
      to.shift(size, size)
      ];
    var polyline = this._createPolyline(pts, color, color, size);
    //polyline.title = title;
    polyline.className = 'cv-poly';
    this._addTitle(polyline, title);
    return polyline;
  },
  /*
   * Draw a box around points
   * - from, to: [x#,y#]  // diagonal 
   * - style: optional {'color':$,'size':#,'fill':$,'title':$}  // size: border size, default 1
   */
  box:function(from, to, style) {
    style = (style) ? style : {};
    var pts = Pt.createEndPts(from, to, this.graph);
    var size = denull(style.size, 1);
    var color = denull(style.color, 'black');
    var fillcolor = (style.fill) ? style.fill : null;
    var polyline = this._createPolyline(pts, color, fillcolor, size);
    if (style.title) {
      //polyline.title = style.title;
      polyline.style.backgroundColor = color;
      polyline.className = 'cv-poly-box';
      this._addTitle(polyline, style.title);
    }
    return polyline;
  },
  /*
   * Draw a line 
   * - from, to: [x#,y#]
   * - style: optional {'color':$,'size':#}  // size: line thickness, default 1
   */
  line:function(from, to, style) {
    style = (style) ? style : {};
    var pts = Pt.createEndPts(from, to, this.graph);
    var pt1 = pts[0];
    var pt2 = pts[1];
    var size = denull(style.size, 1);
    var color = denull(style.color, 'black');
    var line = this._createLine(pts, color, size);
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
  _prepareCanvas:function() {
    this.div.style.position = 'absolute';
    this.div.style.margin = '0';
    this.div.style.padding = '0';
    var dim = Canvas_SVG.getPos(this.div);
    dim.height = this.div.offsetHeight;
    dim.width = this.div.offsetWidth;
    this.div.dim = dim;
  }
};
//
Canvas_SVG.LINE_COLOR_LITE = '#C1E1FF';
Canvas_SVG.LINE_COLOR_BOLD = '#87C5FF';
//Canvas.LINE_COLOR_LITE = 'green';
//Canvas.LINE_COLOR_BOLD = 'green';
//Canvas.LINE_COLOR_LITE = '#63A800';
//Canvas.LINE_COLOR_BOLD = '#63A800';
 /*
  * Get absolute position of element
  * Returns {'left':#,'top':#}; 
  */
Canvas_SVG.getPos = function(e) {
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
/**
 * Point object
 */
/*
 * Convert coords to screen x,y values
 * - coords: [x#,y#]
 * - graph: optional, range values {'xRange':[#start,#end],'yRange':[#start,#end]}
 */
function Pt(coords, graph) {
  this.coords = coords;
  this.cx = coords[0];
  this.cy = coords[1];
  this.graph = graph;
  if (graph) {
    this.x = adjustToRange(this.cx, graph.xRange, 'x');
    this.y = adjustToRange(this.cy, graph.yRange, 'y');
  } else {
    this.x = this.cx;
    this.y = this.cy;
  }
  function adjustToRange(value, range, label) {
    if (value < range.values[0] || value > range.values[1]) {
      throw new Error('Value ' + label + '=' + value + ' out of range.');
    }
    var pos = range.pos.start + (value - range.values[0]) * range.pos.inc;
    return pos; 
  }
}
//
Pt.prototype = {
  shift:function(xAdj, yAdj) {
    this.x += xAdj;
    this.y += yAdj;
    return this;
  },
  coords:function() {
    return this.coords;
  },
  toString:function() {
    return this.x + ',' + this.y;
  },
  toTitle:function() {
    var a = [];
    if (this.graph && this.graph.xRange.label) {
      a.push(this.graph.xRange.label + ': ');
    }
    a.push(Pt.format(this.cx, this.graph.xRange.labelFn));
    a.push(', ');
    if (this.graph && this.graph.yRange.label) {
      a.push(this.graph.yRange.label + ': ');
    }
    a.push(Pt.format(this.cy, this.graph.yRange.labelFn));
    return '(' + a.join('') + ')';
  }
}
//
/*
 * Returns [ptFrom,ptTo]
 */
Pt.createEndPts = function(from, to, graph) {
  var pf = new Pt(from, graph);
  var pt = new Pt(to, graph);
  if (pf.x < pt.x) {
    return [pf, pt];
  } else {
    return [pt, pf];
  }
}
Pt.format = function(n, fn) {
  if (fn) {
    return fn(n);
  }
  var a = (n + '').split('.');
  if (a.length == 2) {
    a[1] = a[1].substr(0, 1);
  }
  return a.join('.');
}
