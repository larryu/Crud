<app-sidebar-layout>
	<app-page-header>
		<div class="col-xs-12">
			<h1 translate>{{ '{{' }} translateKey + 'module-name-plural' }}</h1>
		</div>
	</app-page-header>
	
	<app-page-content>
		<app-box>
			<app-box-body>

				<app-alerts [appMessage]="appMessages$ | async"></app-alerts>
				
				<div class="row">
					<!-- buttons -->
					<div class="col-sm-6 col-md-8 m-b-md">
						<a [routerLink]="[ '/{{ $gen->slugEntityName() }}/create' ]" class="btn btn-primary">
							<i class="glyphicon glyphicon-plus"></i>
							<span translate>{{ '{{' }} translateKey + 'create' }}</span>
						</a>
					</div>

					<!-- basic search -->
					<div class="col-sm-6 col-md-4 m-b-md">
						<{{ str_replace(['.ts', '.'], ['', '-'], $gen->componentFile('search-basic', false)) }} (search)="onSearch($event)" (filterBtnClick)="showSearchOptions = !showSearchOptions"></{{ str_replace(['.ts', '.'], ['', '-'], $gen->componentFile('search-basic', false)) }}>
					</div>

					<!-- search options modal -->
					<div *ngIf="showSearchOptions && formConfigured"
						bsModal
						#staticModal="bs-modal"
						[config]="{ show: true }"
						(onHidden)="showSearchOptions = !showSearchOptions"
						class="modal fade"
						tabindex="-1"
						role="dialog"
						aria-hidden="true">
					  <div class="modal-dialog modal-lg">
					    <div class="modal-content">
					      
					      <div class="modal-header">
					        <h4 class="modal-title pull-left" translate>{{ '{{ ' }}translateKey + 'advanced_search.title' }}</h4>
					        <button type="button" class="close pull-right" aria-label="Close" (click)="staticModal.hide()">
					          <span aria-hidden="true">&times;</span>
					        </button>
					      </div>
					      
					      <div class="modal-body">
					        <{{ str_replace(['.ts', '.'], ['', '-'], $gen->componentFile('search-advanced', false)) }}
					        	class="advanced-search-form"
					        	(search)="onAdvancedSearch($event); staticModal.hide();"
					        	[formModel]="advancedSearchFormModel"
					        	[formData]="{{ $formData = camel_case($gen->entityName()).'FormData$' }} | async"
					        	[form]="advancedSearchForm"
					        	[errors]="{{ $errors = 'errors$' }} | async"
					        	[debug]="false">
					        </{{ str_replace(['.ts', '.'], ['', '-'], $gen->componentFile('search-advanced', false)) }}>
					      </div>

					    </div>
					  </div>
					</div>
				</div>

				<{{ str_replace(['.ts', '.'], ['', '-'], $gen->componentFile('table', $plural = true)) }}
					(updateSearch)="onSearch($event)"
					[{{ camel_case($gen->entityName(true)) }}]="({{ camel_case($gen->entityName(true)) }}Pagination$ | async)?.data"
					[pagination]="({{ camel_case($gen->entityName(true)) }}Pagination$ | async)?.pagination"
					[orderBy]="searchQuery.orderBy"
					[sortedBy]="searchQuery.sortedBy"
					[columns]="searchQuery.filter"
					(deleteBtnClicked)="deleteRow($event)">
				</{{ str_replace(['.ts', '.'], ['', '-'], $gen->componentFile('table', $plural = true)) }}>
			</app-box-body>
		</app-box>
	</app-page-content>
</app-sidebar-layout>