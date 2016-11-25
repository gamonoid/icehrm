Raphael.fn.connection = function (obj1, obj2, line, bg) {
    if (obj1.line && obj1.from && obj1.to) {
        line = obj1;
        obj1 = line.from;
        obj2 = line.to;
    }
    var bb1 = obj1.getBBox(),
        bb2 = obj2.getBBox(),
        p = [{x: bb1.x + bb1.width / 2, y: bb1.y - 1},
        {x: bb1.x + bb1.width / 2, y: bb1.y + bb1.height + 1},
        {x: bb1.x - 1, y: bb1.y + bb1.height / 2},
        {x: bb1.x + bb1.width + 1, y: bb1.y + bb1.height / 2},
        {x: bb2.x + bb2.width / 2, y: bb2.y - 1},
        {x: bb2.x + bb2.width / 2, y: bb2.y + bb2.height + 1},
        {x: bb2.x - 1, y: bb2.y + bb2.height / 2},
        {x: bb2.x + bb2.width + 1, y: bb2.y + bb2.height / 2}],
        d = {}, dis = [];
    for (var i = 0; i < 4; i++) {
        for (var j = 4; j < 8; j++) {
            var dx = Math.abs(p[i].x - p[j].x),
                dy = Math.abs(p[i].y - p[j].y);
            if ((i == j - 4) || (((i != 3 && j != 6) || p[i].x < p[j].x) && ((i != 2 && j != 7) || p[i].x > p[j].x) && ((i != 0 && j != 5) || p[i].y > p[j].y) && ((i != 1 && j != 4) || p[i].y < p[j].y))) {
                dis.push(dx + dy);
                d[dis[dis.length - 1]] = [i, j];
            }
        }
    }
    if (dis.length == 0) {
        var res = [0, 4];
    } else {
        res = d[Math.min.apply(Math, dis)];
    }
    var x1 = p[res[0]].x,
        y1 = p[res[0]].y,
        x4 = p[res[1]].x,
        y4 = p[res[1]].y;
    dx = Math.max(Math.abs(x1 - x4) / 2, 10);
    dy = Math.max(Math.abs(y1 - y4) / 2, 10);
    var x2 = [x1, x1, x1 - dx, x1 + dx][res[0]].toFixed(3),
        y2 = [y1 - dy, y1 + dy, y1, y1][res[0]].toFixed(3),
        x3 = [0, 0, 0, 0, x4, x4, x4 - dx, x4 + dx][res[1]].toFixed(3),
        y3 = [0, 0, 0, 0, y1 + dy, y1 - dy, y4, y4][res[1]].toFixed(3);
    var path = ["M", x1.toFixed(3), y1.toFixed(3), "C", x2, y2, x3, y3, x4.toFixed(3), y4.toFixed(3)].join(",");
    if (line && line.line) {
        line.bg && line.bg.attr({path: path});
        line.line.attr({path: path});
    } else {
        var color = typeof line == "string" ? line : "#000";
        return {
            bg: bg && bg.split && this.path(path).attr({stroke: bg.split("|")[0], fill: "none", "stroke-width": bg.split("|")[1] || 3}),
            line: this.path(path).attr({stroke: color, fill: "none"}),
            from: obj1,
            to: obj2
        };
    }
};

Object.size = function(obj) {
    var size = 0, key;
    for (key in obj) {
        if (obj.hasOwnProperty(key)) size++;
    }
    return size;
};

function HierarchyJs() {
	this.levelItems = {};
	this.calcualtedItems = {};
}

HierarchyJs.method('createNodes', function(data) {

	var nodes = {};
	for(var i=0; i< data.length; i++){
		if(data[i]['children'] == undefined || data[i]['children'] == null){
			data[i]['children'] = {};
		}
		if(data[i].parent == null){
			nodes[data[i].id] = data[i];
		}
	}
	var topNodeDiff = 1;
	while(topNodeDiff != 0){
		var topNodsAtStart = Object.size(nodes);
		for(var i=0; i< data.length; i++){
			if(data[i].parent != null){
				var parentNode = this.findNode(nodes, data[i].parent);
				if(parentNode == null){
					nodes[data[i].id] = data[i];
				}else{
					parentNode['children'][data[i].id] = data[i];
					delete nodes[data[i].id];
				}
			}
		}
		var topNodsAtEnd = Object.size(nodes);
		topNodeDiff = topNodsAtEnd - topNodsAtStart;
	}
	
	return nodes;
});

HierarchyJs.method('findNode', function(nodes, id) {
	if(Object.size(nodes) == 0){
		return null;
	}
	if(!(id in nodes)){
		for(var key in nodes){
	        if(nodes.hasOwnProperty(key)){
	            var ret = this.findNode(nodes[key].children, id);
	            if(ret != null){
	            	return ret;
	            }
	        }
	    }
		return null;
	}else{
		return nodes[id];
	}
	
});



HierarchyJs.method('createHierarchy', function(nodes, r) {
	var connections = [];
	var shapes = [];
	var texts = [];
	var width = 60;
	var height = 40;
	var top = 50;
	var left = 50;
	var topInc = 60;
	var leftInc = 110;
	
	/*
	var el;
	var dragger = function () {
	    this.ox = this.type == "rect" ? this.attr("x") : this.attr("cx");
	    this.oy = this.type == "rect" ? this.attr("y") : this.attr("cy");
	    
	    this.enObj.ox = this.ox;
	    this.enObj.oy = this.oy;
	    
	    this.animate({"fill-opacity": .7}, 500);
	},
	move = function (dx, dy) {
	    var att = this.type == "rect" ? {x: this.ox + dx, y: this.oy + dy} : {cx: this.ox + dx, cy: this.oy + dy};
	    this.attr(att);
	    for (var i = connections.length; i--;) {
	        r.connection(connections[i]);
	    }
	    r.safari();
	},
	up = function () {
	    this.animate({"fill-opacity": 0}, 500);
	};
	*/

	for(var key in nodes){
        if(nodes.hasOwnProperty(key)){
        	this.createHierarchyForNode(nodes[key], r, shapes, connections, texts, top, left, topInc, leftInc, width, height, null, 0);
        }
    }
	
	for (var i = 0, ii = shapes.length; i < ii; i++) {
		var shape = shapes[i];
		var color = Raphael.getColor();
        shape.attr({fill: color, stroke: color, "fill-opacity": 0.6, "stroke-width": 2, cursor: "pointer"});
        //shapes[i].drag(move, dragger, up);
        this.calculateLevelItemPosition(shape);
        
        var text = r.text(shape.nx+(shape.attrs.width/2), shape.ny + shape.attrs.height + 10, shapes[i].textVal);
        
	}
	
	for (var i = 0, ii = shapes.length; i < ii; i++) {
		if(shapes[i].parent != null){
			connections.push(r.connection(shapes[i].parent, shapes[i], "#000", "#fff"));
	    }
	}
	
});

HierarchyJs.method('calculateLevelItemPosition', function(shape) {
	level = shape.level;
	if(this.calcualtedItems[level] == undefined){
		this.calcualtedItems[level] = [];
	}
	this.calcualtedItems[level].push(shape);
	
	var totItems = this.levelItems[level].length;
	var curItems = this.calcualtedItems[level].length;
	
	var canvesWidth = 800;
	
	var widthForItem = canvesWidth/totItems;
	var itemLeft = widthForItem/2 - shape.attrs.width + widthForItem * (curItems - 1);
	shape.nx = itemLeft;
	shape.ny = shape.attrs.y;
	var tHor = itemLeft - shape.attrs.x;
	shape.transform("t"+tHor+",0");
	
});


HierarchyJs.method('createHierarchyForNode', function(node, r, shapes, connections, texts, top, left, topInc, leftInc, width, height, parent, level) {
	var newTop = top + (level * topInc);
	if(this.levelItems[level] == undefined){
		this.levelItems[level] = [];
	}
	
	var shape = r.rect(left, newTop, width, height, 2);
	this.levelItems[level].push(shape);
	shape.textVal = node.title;
	shape.level = level;
	shape.parent = parent;
	shapes.push(shape);
	
	level++;
	for(var key in node.children){
        if(node.children.hasOwnProperty(key)){
    		left = left + leftInc;
    		this.createHierarchyForNode(node.children[key],r, shapes, connections, texts, newTop, left, topInc, leftInc, width, height, shape, level);
        }
    }
	
});




