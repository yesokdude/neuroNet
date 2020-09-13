<!DOCTYPE html>
<html>
<head>
	<title>faceApp</title>
	<link rel="stylesheet" type="text/css" href="/media/css/main.css">
	<script src="brain.js/dist/brain-browser.min.js"></script>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
	<style>
		body {
			background-color: #2b2a2a;
		}

		#canv {
			position: absolute;
			top: 0; right: 0; bottom: 0;left: 0;
			margin: auto;
			background-color: white;

		}

		.block1 {
			position: absolute;
			border: 3px solid black;
			background-color: #d4d4d4;
			padding: 10px;
			width: 325px;
			height: 400px;
			margin-left: 5%;
			margin-top: 1%;
			display: inline-block; /* Строчно-блочный элемент */
    		overflow: hidden; 
		}


		.block2 {	

			position: absolute;
			border: 3px solid black;
			background-color: #d4d4d4;
			padding: 10px;
			width: 325px;
			height: 165px;
			margin-left: 5%;
			margin-top: 30%;
		
		}

		.block3 {
			position: absolute;
			border: 3px solid black;
			background-color: #d4d4d4;
			padding: 10px;
			width: 325px;
			height: 250px;
			margin-left: 75%;
			margin-top: 1%;
		}

	</style>
</head>
<body>

	<div class="block1">
		<center>Нейросеть определяет что нарисовал пользователь, основываясь на полученном опыте предыдущих рисунков.
		Необходимо выбрать 2 категории рисунков, которые будут предоставлены для определения нейросетью, например, грустные и веселые смайлики. Далее, нарисуйте поочередно минимум по 10 изображений для каждой категории, выбирая к какой категории относится ваш рисунок. После обучения, нарисуйте контрольный рисунок и позвольте нейросети определить что вы нарисовали. </center>
	</div>

	<div class="block2">
		<strong>Горячие клавиши:</strong></br>
		
		<strong>V</strong> — запомнить рисунок и выбрать категорию. </br>
		<strong>B</strong> — результат рисунка. </br>
		<strong>C</strong> — очистить поле. </br>
		<small>Клавиши чувствительны к раскладке!</small>
	</div>

	<div class="block3">
		<strong>Инструкция:</strong> </br>

		1. Нарисуйте изображение и нажмите клавишу V для выбора категории.</br>
		2. После выбора категории рисунка, нажмите C, чтобы очистить поле.</br>
		3. Как только вы обучите нейросеть, нарисуйте случайный рисунок из двух категорий на выбор.</br>
		4. Нажмите B для получения результата.</br>
	</div>


	<canvas id="canv" style="display: block;">Ваш браузер устарел</canvas>

	<script>
		function DCanvas(el)
		{
			const ctx = el.getContext('2d');
			const pixel = 20;

			let is_mouse_down = false;

			canv.width = 500;
			canv.height = 500;

			this.drawLine = function(x1, y1, x2, y2, color = 'gray') {
				ctx.beginPath();
				ctx.strokeStyle = color;
				ctx.lineJoin = 'miter';
				ctx.lineWidth = 1;
				ctx.moveTo(x1, y1);
				ctx.lineTo(x2, y2);
				ctx.stroke();
			}

			this.drawCell = function(x, y, w, h) {
				ctx.fillStyle = 'blue';
				ctx.strokeStyle = 'blue';
				ctx.lineJoin = 'miter';
				ctx.lineWidth = 1;
				ctx.rect(x, y, w, h);
				ctx.fill();
			}

			this.clear = function() {
				ctx.clearRect(0, 0, canv.width, canv.height);
			}

			this.drawGrid = function() {
				const w = canv.width;
				const h = canv.height;
				const p = w / pixel;

				const xStep = w / p;
				const yStep = h / p;

				for( let x = 0; x < w; x += xStep )
				{
					this.drawLine(x, 0, x, h);
				}

				for( let y = 0; y < h; y += yStep )
				{
					this.drawLine(0, y, w, y);
				}
			}

			this.calculate = function(draw = false) {
				const w = canv.width;
				const h = canv.height;
				const p = w / pixel;

				const xStep = w / p;
				const yStep = h / p;

				const vector = [];
				let __draw = [];

				for( let x = 0; x < w; x += xStep )
				{
					for( let y = 0; y < h; y += yStep )
					{
						const data = ctx.getImageData(x, y, xStep, yStep);

						let nonEmptyPixelsCount = 0;
						for( i = 0; i < data.data.length; i += 10 )
						{
							const isEmpty = data.data[i] === 0;

							if( !isEmpty )
							{
								nonEmptyPixelsCount += 1;
							}
						}

						if( nonEmptyPixelsCount > 1 && draw )
						{
							__draw.push([x, y, xStep, yStep]);
						}

						vector.push(nonEmptyPixelsCount > 1 ? 1 : 0);
					}
				}

				if( draw )
				{
					this.clear();
					this.drawGrid();

					for( _d in __draw )
					{
						this.drawCell( __draw[_d][0], __draw[_d][1], __draw[_d][2], __draw[_d][3] );
					}
				}

				return vector;
			}

			el.addEventListener('mousedown', function(e) {
				is_mouse_down = true;
				ctx.beginPath();
			})

			el.addEventListener('mouseup', function(e) {
				is_mouse_down = false;
			})

			el.addEventListener('mousemove', function(e) {
				if( is_mouse_down )
				{
					ctx.fillStyle = 'red';
					ctx.strokeStyle = 'red';
					ctx.lineWidth = pixel;

					ctx.lineTo(e.offsetX, e.offsetY);
					ctx.stroke();

					ctx.beginPath();
					ctx.arc(e.offsetX, e.offsetY, pixel / 2, 0, Math.PI * 2);
					ctx.fill();

					ctx.beginPath();
					ctx.moveTo(e.offsetX, e.offsetY);
				}
			})
		}

		let vector = [];
		let net = null;
		let train_data = [];

		const d = new DCanvas(document.getElementById('canv'));

		document.addEventListener('keypress', function(e) {
			if( e.key.toLowerCase() == 'c' )
			{
				d.clear();
			}

			if( e.key.toLowerCase() == 'v' )
			{
				vector = d.calculate(true);
				
				//train
				if( confirm('Русь?') )
				{
					train_data.push({
						input: vector,
						output: {'Свастика. Слава России!': 1}
					});
				} else
				{
					train_data.push({
						input: vector,
						output: {'Герб хохлов.': 1}
					});
				}
			}

			if( e.key.toLowerCase() == 'b' )
			{
				net = new brain.NeuralNetwork();
				net.train(train_data, {log: true});

				const result = brain.likely(d.calculate(), net);
				alert(result);
			}
		});
	</script>


	<div id="particles-js"></div>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js" defer></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/particles.js/2.0.0/particles.min.js" defer></script>
	<script src="/media/js/main.js" defer></script>
</body>
</html>