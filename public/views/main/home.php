<div id="HomePage">

	<!-- Parte de boas vindas - Banner e texto explicativo -->
	<div class="w3-row w3-padding-large">
		<div class="w3-content">
			<a href="#" data-toggle="modal" data-target="#CriarSuaLojinha">
				<img class="w3-image w3-round" src="{{imgFolder}}site/banners/banner-inicial.png">
			</a>
			<h3>Seja bem vindo ao Crescendo e Passando!</h3>
			<p>AQUI VOCÊ PODE COMPRAR E VENDER ROUPAS, SAPATOS, BRINQUEDOS, MÓVEIS E ACESSÓRIOS QUASE NOVOS OU NUNCA USADOS PARA BEBÊS, CRIANÇAS, ADOLESCENTES ATÉ 18 ANOS E MAMÃES.</p>
		</div>
	</div>

	<!-- Produtos em destaque -->
	<div name="featuredProducts" ng-show="vm.featuredProducts.length > 0">
		<div class="w3-row bg-color-2y w3-padding">
			<h4>PRODUTOS EM DESTAQUE</h4>
		</div>

		<div class="w3-row w3-padding">
			<div ng-repeat="produto in vm.featuredProducts">
				<a ui-sref="root.product(produto)">
					<div class="col-md-2 w3-card padding-20" ng-class="(j==1)?'col-md-offset-2':'hidden-sm hidden-xs'">
						<img class="img-responsive no-margin" src="{{imgFolder}}site/products/{{produto.filename}}" width="200px" height="200px">
						<span>{{produto.name}}</span> <br>
						Marca: {{produto.brand}}<br>
						<span style="color:#87CEEB">R$ {{produto.price | number: 2}}</span> <br>
						<span style="color:#fec860">12x R$ {{produto.price / 12 | number: 2}}</span> <br>
					</div>
				</a>
			</div>
		</div>

	</div>

	<!-- Lojas em destaque -->
	<div name="featuredStores" ng-show="vm.featuredStores.length > 0">
		<div class="w3-row bg-color-2y w3-padding">
			<h4>LOJINHAS EM DESTAQUE</h4>
		</div>

		<div class="w3-row w3-padding">
			<div ng-repeat="store in vm.featuredStores">
				<a ui-sref="root.store(store)">
					<div class="col-md-2 w3-card padding-20" ng-class="(j==1)?'col-md-offset-2':'hidden-sm hidden-xs'">
						<img class="img-responsive no-margin" src="{{imgFolder}}stores/logo/{{store.profile_image}}" width="200px" height="200px">
						{{store.name}} <br>
						<i class="fa fa-tags" aria-hidden="true"></i> {{store.n_produtos}} publicados<br>
						<i class="fa fa-gift" aria-hidden="true"></i> {{store.sales}} vendidos<br>
					</div>
				</a>
			</div>
		</div>
	</div>
	</div>

<!-- Crie sua lojinha modal -->
<modal class="text-center" modal-id="CriarSuaLojinha" modal-type="md" modal-title="Criar sua lojinha ficou fácil">
	<?php
		readfile('modal-content/CriarSuaLojinha.html');
	 ?>
</modal>
