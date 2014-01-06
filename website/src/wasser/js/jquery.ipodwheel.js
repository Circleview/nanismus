
$(function() {
/*Einstellungen am iPod Wheel*/

    $(".knob").knob({
        /*change : function (value) {
            //console.log("change : " + value);
        },
        release : function (value) {
            console.log("release : " + value);
        },
        cancel : function () {
            console.log("cancel : " + this.value);
        },*/
        draw : function () {

            // "tron" case
            if(this.$.data('skin') == 'tron') {

                var a = this.angle(this.cv)  // Angle
                    , sa = this.startAngle          // Previous start angle
                    , sat = this.startAngle         // Start angle
                    , ea                            // Previous end angle
                    , eat = sat + a                 // End angle
                    , r = 1;

                this.g.lineWidth = this.lineWidth;

                this.o.cursor
                    && (sat = eat - 0.3)
                    && (eat = eat + 0.3);

                if (this.o.displayPrevious) {
                    ea = this.startAngle + this.angle(this.v);
                    this.o.cursor
                        && (sa = ea - 0.3)
                        && (ea = ea + 0.3);
                    this.g.beginPath();
                    this.g.strokeStyle = this.pColor;
                    this.g.arc(this.xy, this.xy, this.radius - this.lineWidth, sa, ea, false);
                    this.g.stroke();
                }

                this.g.beginPath();
                this.g.strokeStyle = r ? this.o.fgColor : this.fgColor ;
                this.g.arc(this.xy, this.xy, this.radius - this.lineWidth, sat, eat, false);
                this.g.stroke();

                this.g.lineWidth = 2;
                this.g.beginPath();
                this.g.strokeStyle = this.o.fgColor;
                this.g.arc( this.xy, this.xy, this.radius - this.lineWidth + 1 + this.lineWidth * 2 / 3, 0, 2 * Math.PI, false);
                this.g.stroke();

                return false;
            }
        }
    });

    // Example of infinite knob, iPod click wheel
    var v, up=0,down=0,i=0,sprung=40,minmenge=0,maxmenge=500 // vorerst nicht mehr maxmenge als 500!, da sonst die PHP Übertragung nicht klappt!
        ,$idir = $("div.idir")
		,$ishow = $("div.ishow")
        ,$ival = $("textarea.ival")
        ,incr = function() { if(i >= maxmenge) {
			i = maxmenge
		}
		else { i = i + sprung}; $idir.show().html("+").fadeOut(); if(i <= maxmenge){$ival.html(i)
		$ishow.html(i + " ml")}
		else {
			$ival.html(maxmenge)
			$ishow.html(maxmenge + " ml")
			}; }
        ,decr = function() { if(i <= minmenge) {
			i = minmenge
			} else { i = i - sprung}; $idir.show().html("-").fadeOut(); if(i >= minmenge) {
				$ival.html(i)
				$ishow.html(i + " ml")
			} else {
				$ival.html(minmenge)
				$ishow.html(minmenge + " ml")
				}; };
    $("input.infinite").knob(
                        {
                        min : 0
                        , max : 20
                        , stopper : false
                        , change : function () {
                                        if(v > this.cv){
                                            if(up){
                                                decr();
                                                up=0;
                                            }else{up=1;down=0;}
                                        } else {
                                            if(v < this.cv){
                                                if(down){
                                                    incr();
                                                    down=0;
                                                }else{down=1;up=0;}
                                            }
                                        }
                                        v = this.cv;
                                    }
                        });
});