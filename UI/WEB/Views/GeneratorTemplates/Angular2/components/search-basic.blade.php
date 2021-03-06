import { Component, EventEmitter, Input, OnInit, Output } from '@angular/core';
import { FormGroup, FormBuilder, ReactiveFormsModule } from '@angular/forms';

import { {{ $entitySin = $gen->entityName() }} } from './../../models/{{ camel_case($entitySin) }}';

{{ '@' }}Component({
  selector: '{{ str_replace(['.ts', '.'], ['', '-'], $gen->componentFile('search-basic', false)) }}',
  templateUrl: './{{ $gen->componentFile('search-basic-html', false) }}',
  styleUrls: ['./{{ $gen->componentFile('search-basic-css', false) }}']
})
export class {{ $gen->componentClass('search-basic', $plural = false) }} implements OnInit {
  @Output()
  public search = new EventEmitter<{}>();

  @Output()
  public filterBtnClick = new EventEmitter<null>();
  
  public searchForm: FormGroup;

  public constructor(private fb: FormBuilder) { }

  public ngOnInit() {
  	this.searchForm = this.fb.group({
      search: [''],
      page: [1]
    });
  }
}
