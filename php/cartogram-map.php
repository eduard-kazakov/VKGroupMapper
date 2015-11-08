<?php
    if (isset ($_GET['data_source'])) {
        $data_source = $_GET['data_source'];
    } else {
        return 0;
    };
    
    if (isset ($_GET['number_of_classes'])) {
        $number_of_classes = $_GET['number_of_classes'];
    } else {
        return 0;
    };
    
    if (isset ($_GET['range_type'])) {
        $range_type = $_GET['range_type'];
    } else {
        return 0;
    };
    
    if (isset ($_GET['public_id'])) {
        $public_id = $_GET['public_id'];
    } else {
        return 0;
    };
?>

<!DOCTYPE html>
<html>
<head>
	<title>Карта участников сообщества VK</title>
	<meta charset="utf-8" />

	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet/v0.7.7/leaflet.css" />

	<style>
		#map { position:absolute; top:0; bottom:0; width:100%; }

		.info {
			padding: 6px 8px;
			font: 14px/16px Arial, Helvetica, sans-serif;
			background: white;
			background: rgba(255,255,255,0.8);
			box-shadow: 0 0 15px rgba(0,0,0,0.2);
			border-radius: 5px;
		}
		.info h4 {
			margin: 0 0 5px;
			color: #777;
		}

		.legend {
			text-align: left;
			line-height: 18px;
			color: #555;
		}
		.legend i {
			width: 18px;
			height: 18px;
			float: left;
			margin-right: 8px;
			opacity: 0.7;
		}
	</style>
</head>
<body>
	<div id="map"></div>

	<script src="http://cdn.leafletjs.com/leaflet/v0.7.7/leaflet.js"></script>
    
    <script src="http://requirejs.org/docs/release/2.1.11/minified/require.js"></script>
    

    <!-- Источник геоданных -->
	<?php
        echo "<script type=\"text/javascript\" src=\"$data_source\"></script>";
    ?>

	<script type="text/javascript">
        
        <?php
            //echo "states = require(\"$data_source\");"
        ?>
        
        
        
        // Собираем статистику по исходному набору геоданных
        var allValues = new Array();
        <?php
            if ($range_type == 0) {
                echo "var range_type = 0;";
            } else {
                echo "var range_type = 1;";
            }
        ?>
        // Из каждого объекта выписываем в массив плотность участников
        function onEachFeatureStartWalking(feature, layer) {
            if (feature.properties.SUM_count >= 0) {
                allValues.push(feature.properties.SUM_count * 1000 / feature.properties.pop);
            }
		}
        // Стартовое открытие набора геоданных для извлечения статистики
        geojsonStartWalking = L.geoJson(statesData, {
			onEachFeature: onEachFeatureStartWalking
		})
        
        allValues = allValues.sort(function (a, b) { 
            return a - b;
        });
        
        
        // Расчитываем грани в зависимости от числа классов и типа распределения
        
        var allGrades = new Array();
        
        function getPercentile(arr, p) {
            if (arr.length === 0) return 0;
            if (typeof p !== 'number') throw new TypeError('p must be a number');
            if (p <= 0) return arr[0];
            if (p >= 1) return arr[arr.length - 1];
        
            var index = arr.length * p,
                lower = Math.floor(index),
                upper = lower + 1,
                weight = index % 1;
        
            if (upper >= arr.length) return arr[lower];
            return arr[lower] * (1 - weight) + arr[upper] * weight;
        }
        
        function getQuantileGrades (values, classes_number) {
            interval = 1.0 / classes_number;
            grades = new Array();
            i = interval;
            while (i < 0.999) {
                grades.push(getPercentile(allValues,i).toFixed(3));
                i = i + interval;
            }
            return grades;
        }
        
        function getEqualGrades (values, classes_number) {
            max = Math.max.apply(null, values);
            min = Math.min.apply(null, values);
            interval = (max - min) / classes_number;
            grades = new Array();
            i = min + interval;
            while (i < max) {
                grades.push(i.toFixed(3));
                i = i + interval;
            }
            return grades;
        }        
        
        function negativeToZero (value) {
            if (value < 0) {
                return 0;
            } else {
                return value;
            }
        };
        
        console.log (getEqualGrades(allValues,6));
        console.log (getQuantileGrades(allValues,6));
        ////////////////////////////
        ////////////////////////////
        ////////////////////////////
        
        
		var map = L.map('map').setView([64, 97], 12);
        
        
        L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token=pk.eyJ1IjoibWFwYm94IiwiYSI6IjZjNmRjNzk3ZmE2MTcwOTEwMGY0MzU3YjUzOWFmNWZhIn0.Y8bhBaUMqFiPrDRW9hieoQ', {
			maxZoom: 18,
			attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, ' +
				'<a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' +
				'Imagery © <a href="http://mapbox.com">Mapbox</a>',
			id: 'mapbox.light'
		}).addTo(map);

        var southWest = L.latLng(40, 19),
            northEast = L.latLng(81, 177),
            russiaBounds = L.latLngBounds(southWest, northEast);

        // Zoom to Russia
        map.fitBounds(russiaBounds);

		// control that shows state info on hover
		var info = L.control();

		info.onAdd = function (map) {
			this._div = L.DomUtil.create('div', 'info');
			this.update();
			return this._div;
		};

        <?php
            echo "info.update = function (props) {";
            echo	"this._div.innerHTML = \"<h4><a href ='../index.html'>Вернуться на главную страницу</a></h4><br><h4>География расселения участников группы $public_id</h4>\" +  (props ?";
            echo	"'<b>' + props.NAME + '</b><br />' + (negativeToZero(props.SUM_count) * 1000 / props.pop).toFixed(3) + ' участников на 1000 человек населения'";
            echo	": 'Разместите мышь над субъектом РФ для получения деталей');";
            echo "};";
        ?>
        
		info.addTo(map);        
        
        // получить массив цветов из числа классов
        function getColorGrades (classes) {
            allColorGrades = new Array('#FED976','#FEB24C','#FD8D3C','#FC4E2A','#E31A1C','#BD0026','#800026');
            if (classes == 3) {
                colorGrades = new Array (allColorGrades[0],allColorGrades[3],allColorGrades[6]);
            };
            if (classes == 4) {
                colorGrades = new Array (allColorGrades[0],allColorGrades[2],allColorGrades[4],allColorGrades[6]);
            };
            if (classes == 5) {
                colorGrades = new Array (allColorGrades[0],allColorGrades[2],allColorGrades[3],allColorGrades[4],allColorGrades[6]);
            };
            if (classes == 6) {
                colorGrades = new Array (allColorGrades[0],allColorGrades[1],allColorGrades[2],allColorGrades[4],allColorGrades[5],allColorGrades[6]);
            };
            if (classes == 7) {
                colorGrades = allColorGrades;
            };
            return colorGrades;
        }
        
		// get color depending on population density value
		function getColor(d) {
            <?php
                echo "classes = $number_of_classes;";
            ?>
            colorGrades = getColorGrades(classes);
            if (range_type == 0) {
                grades = getQuantileGrades(allValues,classes);
                } else {
                grades = getEqualGrades(allValues,classes);
                };
            i = 0;
            
            //console.log(colorGrades[0])
            //console.log(colorGrades[classes-1])
            
            //console.log(grades[0])
            //console.log(grades[classes-2])
            
            while (i < classes-2) {
                if (d < grades[0]) {
                    return colorGrades[0]
                }
                if (d > grades[classes-2]) {
                    return colorGrades[classes-1]
                }
                if (d >= grades[i] && d < grades[i+1]) {
                    return colorGrades[i+1];
                }
                i = i + 1;
            };

		}

		function style(feature) {
			return {
				weight: 1,
				opacity: 1,
				color: 'white',
				dashArray: '3',
				fillOpacity: 0.7,
				fillColor: getColor((feature.properties.SUM_count * 1000 / feature.properties.pop))
			};
		}

		function highlightFeature(e) {
			var layer = e.target;

			layer.setStyle({
				weight: 2,
				color: '#666',
				dashArray: '',
				fillOpacity: 0.7
			});

			if (!L.Browser.ie && !L.Browser.opera) {
				layer.bringToFront();
			}

			info.update(layer.feature.properties);
		}

		var geojson;
        
        
		function resetHighlight(e) {
			geojson.resetStyle(e.target);
			info.update();
		}

		function zoomToFeature(e) {
			map.fitBounds(e.target.getBounds());
		}
        
		function onEachFeature(feature, layer) {
			layer.on({
				mouseover: highlightFeature,
				mouseout: resetHighlight,
				click: zoomToFeature
			})
		}

		geojson = L.geoJson(statesData, {
			style: style,
			onEachFeature: onEachFeature
		}).addTo(map);
        
		map.attributionControl.addAttribution('VKGroupMapper, Eduard Kazakov, Gennady Akimov');

		var legend = L.control({position: 'bottomright'});

		legend.onAdd = function (map) {

			var div = L.DomUtil.create('div', 'info legend'),
				<?php 
                if ($range_type == 0) {
                    echo "grades = getQuantileGrades(allValues,$number_of_classes),"; 
                } else {
                    echo "grades = getEqualGrades(allValues,$number_of_classes),"; 
                }
                ?> 
				labels = [],
				from, to;
            //console.log(grades);
			for (var i = 0; i < grades.length; i++) {
            
                if (i == 0) {
                    from = 0;
                    to = grades [0];
                    labels.push('<i style="background:' + getColor((parseFloat(to) + parseFloat(from)) / 2) + '"></i> ' +
                    from + (to ? '&ndash;' + to : '+'));
                    
                    from = grades[0];
                    to = grades [1];
                    labels.push('<i style="background:' + getColor((parseFloat(to) + parseFloat(from)) / 2) + '"></i> ' +
                    from + (to ? '&ndash;' + to : '+'));
                    
                }
                else {
                    from = grades[i];
                    to = grades[i + 1];
                    if ((i + 1) == grades.length) {
                        to = "1" + grades[i];
                        labels.push(
                        '<i style="background:' + getColor((parseFloat(to) + parseFloat(from)) / 2) + '"></i> ' +
                        from + ' +');
                    } else {
                    
                    labels.push(
                        '<i style="background:' + getColor((parseFloat(to) + parseFloat(from)) / 2) + '"></i> ' +
                        from + (to ? '&ndash;' + to : '+'));
                       
                    }
                }
			}

			div.innerHTML = labels.join('<br>');
			return div;
		};

		legend.addTo(map);
	</script>
</body>
</html>
