<template>
  <div
    class="box-merged"
    v-if="(communicationsCancellation.length > 0 && flag_hidden) || !flag_hidden"
  >
    <a-row type="flex" align="start" justify="start" class="mb-2 mt-3">
      <a-col :span="props.type == 'cancellation' ? 24 : 11" v-if="props.type != 'new'">
        <div
          v-bind:class="[
            'box-left',
            !props.show_communication
              ? 'bg-pink-stick p-5 mb-4'
              : props.flag_preview && !props.flag_simulation
                ? ''
                : 'box-bordered p-4',
            props.flag_simulation ? 'my-0' : '',
          ]"
        >
          <div v-if="props.title" :class="['box-title', props.buttons ? 'mx-3' : '']">
            <a-row type="flex" align="middle" justify="start" class="mb-3">
              <!-- a-col class="d-flex" style="gap: 7px">
                <big>
                  <font-awesome-icon
                    :icon="
                      filesStore.showServiceIcon(
                        from.service_category_id,
                        from.service_sub_category_id,
                        from.service_type_id
                      )
                    "
                    style="font-size: 1.3rem"
                  />
                </big>
              </a-col -->
              <a-col flex="auto">
                <a-tooltip>
                  <template #title v-if="props.from.name.length > 100">
                    {{ props.from.name }}
                  </template>
                  <div class="mb-2">
                    <b class="text-700 text-uppercase">
                      {{ truncateString(props.from.name, 100) }}
                    </b>
                  </div>
                </a-tooltip>
              </a-col>
              <a-col>
                <a-tag class="m-0" color="#c63838">
                  {{ props.from.object_code }}
                </a-tag>
              </a-col>
            </a-row>
          </div>

          <div
            v-bind:class="[
              !props.flag_simulation ? (props.flag_preview ? 'px-2' : 'p-3 mt-3 mb-4') : '',
            ]"
          >
            <template v-if="!props.show_communication">
              <a-row
                type="flex"
                align="middle"
                justify="space-between"
                class="mb-3"
                v-if="props.flag_preview"
              >
                <a-col class="d-flex" style="gap: 7px">
                  <big>
                    <font-awesome-icon
                      :icon="
                        filesStore.showServiceIcon(
                          from.service_category_id,
                          from.service_sub_category_id,
                          from.service_type_id
                        )
                      "
                      style="font-size: 1.2rem"
                    />
                  </big>
                  <a-tooltip>
                    <template #title v-if="props.from.name.length > 100">
                      {{ props.from.name }}
                    </template>
                    <big class="ellipsis d-flex"
                      ><b class="text-700">{{ truncateString(props.from.name, 100) }}</b></big
                    >
                  </a-tooltip>
                </a-col>
              </a-row>

              <a-row
                type="flex"
                justify="space-between"
                :class="[props.flag_preview ? 'px-3 pt-2' : '']"
                align="middle"
              >
                <a-col>
                  <a-row type="flex" justify="start" style="gap: 5px">
                    <a-col>
                      <CalendarOutlined />
                    </a-col>
                    <a-col>
                      <b class="text-700">{{ formatDate(props.from.date_in, 'DD/MM/YYYY') }}</b>
                    </a-col>
                  </a-row>
                </a-col>
                <a-col>
                  <b class="text-700 text-capitalize">{{ t('global.label.passengers') }}: </b>
                  <i class="bi bi-person-fill"></i> {{ props.from.adults }}
                  <i class="bi bi-person-arms-up"></i> {{ props.from.children }}
                </a-col>
                <a-col>
                  <a
                    href="javascript:;"
                    v-on:click="showInformation('from', from)"
                    class="d-flex text-danger"
                    style="font-size: 14px; border-bottom: 1px solid; padding-bottom: 1px; gap: 4px"
                    ><span class="text-capitalize">{{ t('global.label.more') }}</span>
                    <span class="text-lowercase"
                      >{{ t('global.label.information') }} {{ t('global.label.of') }}
                      {{ t('global.label.service') }}</span
                    ></a
                  >
                </a-col>
                <a-col class="d-flex" v-if="type == 'cancellation'">
                  <a-row type="flex" align="middle" style="gap: 7px">
                    <a-col>
                      <span class="d-flex" v-if="from.penality > 0">
                        <font-awesome-icon
                          :icon="['fas', 'triangle-exclamation']"
                          size="xl"
                          class="text-warning"
                        />
                      </span>
                      <span class="d-flex" v-else>
                        <font-awesome-icon
                          :icon="['fas', 'circle-check']"
                          size="xl"
                          class="text-success"
                        />
                      </span>
                    </a-col>
                    <a-col>
                      <span
                        v-bind:class="[
                          from.penality > 0 ? 'text-dark-warning' : 'text-success',
                          'text-700',
                        ]"
                      >
                        <template v-if="from.penality > 0">
                          {{ t('files.label.cancellation_with_penalty') }}
                        </template>
                        <template v-else>
                          {{ t('files.label.cancellation_without_penalty') }}
                        </template>
                      </span>
                    </a-col>
                    <a-col v-if="from.penalty > 0">
                      <b class="h5 text-warning mb-0 mx-1"
                        >$ {{ formatNumber({ number: from.penality, digits: 2 }) }}</b
                      >
                    </a-col>
                  </a-row>
                </a-col>
                <a-col v-else>
                  <font-awesome-icon
                    :icon="['fas', 'triangle-exclamation']"
                    size="xl"
                    class="text-warning"
                  />
                  <b>{{ t('global.label.cost') }}:</b>
                  <b class="h5 mb-0 mx-1"
                    >$ {{ formatNumber({ number: props.from.total_amount, digits: 2 }) }}</b
                  >
                </a-col>
              </a-row>
            </template>

            <template v-if="props.show_communication">
              <template v-if="filesStore.isLoadingAsync || !search_communication">
                <a-skeleton active />
              </template>
              <template v-else>
                <template v-if="flag_communication_from">
                  <div v-if="!filesStore.isLoadingAsync && communicationsCancellation.length > 0">
                    <div
                      class="my-3 p-3"
                      style="border: 1px solid #e9e9e9; border-radius: 6px"
                      v-for="(communication, c) in communicationsCancellation"
                    >
                      <a-row type="flex" align="middle" justify="space-between" class="mb-3">
                        <a-col>
                          <a-row type="flex" style="gap: 5px">
                            <a-col>
                              <IconUserConfiguration />
                            </a-col>
                            <a-col>
                              <a-tooltip>
                                <template #title>{{ communication.supplier_name }}</template>
                                <b>{{ communication.code_request_book }}</b>
                              </a-tooltip>
                            </a-col>
                          </a-row>
                        </a-col>
                        <a-col
                          v-if="
                            communication.send_communication &&
                            communication?.supplier_emails?.length > 0
                          "
                        >
                          <!-- span>Correo asociado a reservas:</span -->
                          <span class="text-danger">
                            <a-tooltip>
                              <template #title>
                                <small
                                  class="d-block"
                                  v-for="email in communication.supplier_emails"
                                >
                                  {{ email }}
                                </small>
                              </template>
                              <font-awesome-icon
                                :icon="['fas', 'envelope-circle-check']"
                                class="mx-2"
                              />
                            </a-tooltip>
                          </span>
                          <span
                            class="cursor-pointer"
                            v-on:click="communication.show_emails = !communication.show_emails"
                          >
                            <a-tooltip>
                              <template #title>Ver correos asignados</template>
                              <font-awesome-icon
                                :icon="['far', 'square-plus']"
                                class="text-danger"
                              />
                            </a-tooltip>
                          </span>

                          <modal-add-emails
                            v-if="communication.show_emails"
                            @close="communication.show_emails = !communication.show_emails"
                            @save="saveNote(communication, 'cancellation')"
                            v-model="communication.supplier_emails"
                          />
                        </a-col>
                      </a-row>
                      <div
                        class="bg-pink-stick mb-3"
                        v-for="(component, cp) in communication.components"
                      >
                        <a-row type="flex" align="top" justify="space-between">
                          <a-col>
                            <span class="d-block">
                              <a-tooltip>
                                <template #title v-if="component.name.length > 20">{{
                                  component.name
                                }}</template>
                                {{
                                  type === 'modification'
                                    ? truncateString(component.name, 20)
                                    : truncateString(component.name, 60)
                                }}
                              </a-tooltip>
                            </span>
                          </a-col>
                          <a-col>
                            <a-row type="flex" align="middle" style="gap: 7px">
                              <a-col>
                                <span class="d-flex" v-if="component.penality > 0">
                                  <font-awesome-icon
                                    :icon="['fas', 'triangle-exclamation']"
                                    size="xl"
                                    class="text-warning"
                                  />
                                </span>
                                <span class="d-flex" v-else>
                                  <font-awesome-icon
                                    :icon="['fas', 'circle-check']"
                                    size="xl"
                                    class="text-success"
                                  />
                                </span>
                              </a-col>
                              <a-col>
                                <span
                                  v-bind:class="[
                                    component.penality > 0 ? 'text-dark-warning' : 'text-success',
                                    'text-700',
                                  ]"
                                >
                                  <template v-if="component.penality > 0">
                                    {{ t('files.label.cancellation_with_penalty') }}
                                  </template>
                                  <template v-else>
                                    {{ t('files.label.cancellation_without_penalty') }}
                                  </template>
                                </span>
                              </a-col>
                              <a-col v-if="component.penality > 0">
                                <span
                                  v-bind:class="[
                                    component.penality > 0 ? 'text-warning' : 'text-success',
                                    'text-700',
                                    'h5',
                                    'mb-0',
                                  ]"
                                >
                                  <b
                                    >$
                                    {{ formatNumber({ number: component.penality, digits: 2 }) }}</b
                                  >
                                </span>
                              </a-col>
                            </a-row>
                          </a-col>
                        </a-row>
                      </div>

                      <div class="my-3" v-if="type_from === 'cancellation'">
                        <a-row align="middle" type="flex" justify="space-between" class="mx-2">
                          <a-col>
                            <a-row
                              align="middle"
                              type="flex"
                              justify="start"
                              style="gap: 5px"
                              @click="communication.showNotesFrom = !communication.showNotesFrom"
                            >
                              <a-col>
                                <div
                                  v-bind:class="[
                                    'd-flex cursor-pointer',
                                    !communication.showNotesFrom ? 'text-dark-gray' : 'text-danger',
                                  ]"
                                >
                                  <template v-if="communication.showNotesFrom">
                                    <i
                                      class="bi bi-check-square-fill text-danger d-flex"
                                      style="font-size: 1.5rem"
                                    ></i>
                                  </template>
                                  <template v-else>
                                    <i class="bi bi-square d-flex" style="font-size: 1.5rem"></i>
                                  </template>
                                </div>
                              </a-col>
                              <a-col>
                                <span class="cursor-pointer">{{
                                  t('global.label.add_note_to_provider')
                                }}</span>
                              </a-col>
                            </a-row>
                          </a-col>
                        </a-row>
                        <div class="mb-3 mx-2" v-if="communication.showNotesFrom">
                          <template v-if="communication.lockedNotesFrom">
                            <a-card
                              style="width: 100%"
                              class="mt-3"
                              :headStyle="{ background: black }"
                            >
                              <template #title
                                >{{ t('global.label.note_in_reserve_to_provider') }}:</template
                              >
                              <template #extra>
                                <a
                                  href="javascript:;"
                                  @click="communication.lockedNotesFrom = false"
                                  class="text-danger"
                                >
                                  <i class="bi bi-pencil"></i>
                                </a>
                              </template>
                              <p class="mb-2">
                                <b>{{ communication.notas }}</b>
                              </p>
                              <template v-for="(file, f) in communication.filesFrom" :key="f">
                                <a-row align="middle" class="mb-2">
                                  <i class="bi bi-paperclip"></i>
                                  <a :href="file" target="_blank" class="text-dark mx-1">
                                    {{ showName(file) }}
                                  </a>
                                </a-row>
                              </template>
                            </a-card>
                          </template>

                          <template v-if="!communication.lockedNotesFrom">
                            <p class="text-danger my-2">
                              {{ t('global.label.note_to_provider') }}:
                            </p>
                            <a-row align="top" justify="space-between">
                              <a-col flex="auto">
                                <a-textarea
                                  v-model:value="communication.notas"
                                  :maxlength="100"
                                  show-count
                                  placeholder="Escribe una nota para el proveedor que podrás visualizar en la comunicación"
                                  :auto-size="{ minRows: 2 }"
                                />
                              </a-col>
                              <a-col class="mx-2">
                                <file-upload
                                  v-bind:folder="'communications'"
                                  @onResponseFiles="responseFilesFrom(communication, $event)"
                                />
                              </a-col>
                              <a-col>
                                <a-button
                                  danger
                                  type="default"
                                  size="large"
                                  v-bind:disabled="
                                    !(
                                      communication.notas != '' ||
                                      communication.filesFrom.length > 0
                                    )
                                  "
                                  class="d-flex ant-row-middle text-600"
                                  @click="saveNote(communication, 'cancellation')"
                                  :loading="
                                    filesStore.isLoadingNotes ||
                                    communicationsStore.isLoading ||
                                    communicationsStore.isLoadingAsync
                                  "
                                >
                                  <i
                                    v-bind:class="[
                                      'bi bi-floppy',
                                      filesStore.isLoadingNotes ||
                                      communicationsStore.isLoading ||
                                      communicationsStore.isLoadingAsync
                                        ? 'mx-2'
                                        : '',
                                    ]"
                                  ></i>
                                </a-button>
                              </a-col>
                            </a-row>
                          </template>
                        </div>
                        <template
                          v-if="communication.lockedNotesFrom || !communication.showNotesFrom"
                        >
                          <a-row align="middle" type="flex" justify="end" class="mx-2">
                            <a-col class="ant-row-end">
                              <a-button
                                danger
                                type="default"
                                class="d-flex ant-row-middle text-600"
                                @click="showCommunicationFrom(communication)"
                                size="large"
                                :loading="
                                  filesStore.isLoadingNotes ||
                                  communicationsStore.isLoading ||
                                  communicationsStore.isLoadingAsync
                                "
                              >
                                <i
                                  class="bi bi-search"
                                  v-if="
                                    !(
                                      filesStore.isLoadingNotes ||
                                      communicationsStore.isLoading ||
                                      communicationsStore.isLoadingAsync
                                    )
                                  "
                                ></i>
                                <span class="mx-2">{{ t('global.label.show_communication') }}</span>
                              </a-button>
                            </a-col>
                          </a-row>
                        </template>
                      </div>
                    </div>
                  </div>
                </template>
                <template v-else>
                  <a-empty :description="null" class="py-3 bg-light m-0" />
                </template>
              </template>
            </template>
          </div>
        </div>
      </a-col>
      <template v-if="props.to">
        <a-col
          :span="2"
          class="text-center merge-icon"
          v-if="props.type != 'new' && props.type != 'cancellation'"
        >
          <i
            class="bi bi-arrow-right-circle d-block"
            style="font-size: 4rem; padding-top: 6rem"
          ></i>
        </a-col>
        <a-col :span="props.type != 'modification' ? 24 : 11">
          <div
            v-bind:class="[
              'box-left',
              !props.show_communication
                ? 'bg-purple-stick'
                : props.flag_preview && !props.flag_simulation
                  ? ''
                  : 'box-bordered p-4',
              props.flag_simulation ? 'my-0' : '',
            ]"
            v-for="(_service, s) in to"
            :key="s"
          >
            <a-row align="middle" justify="end" class="mb-3" v-if="_service.emails?.length > 0">
              <!-- span>Correo asociado a reservas:</span -->
              <span class="mx-2 bordered" v-if="type !== 'modification'">{{
                _service.emails[0]
              }}</span>
              <span
                class="cursor-pointer"
                v-on:click="_service.show_emails = !_service.show_emails"
              >
                <a-tooltip>
                  <template #title>Ver correos asignados</template>
                  <font-awesome-icon :icon="['far', 'square-plus']" class="text-danger" />
                </a-tooltip>
              </span>

              <modal-add-emails
                v-if="_service.show_emails"
                @close="_service.show_emails = !_service.show_emails"
                v-model="_service.emails"
              />
            </a-row>
            <div class="box-title mx-3 mb-3">
              <a-row type="flex" align="middle" justify="start" style="gap: 5px">
                <!-- a-col>
                  <big>
                    <font-awesome-icon
                      :icon="
                        filesStore.showServiceIcon(_service.search_parameters_services.category_id)
                      "
                      style="font-size: 1.2rem"
                    />
                  </big>
                </a-col -->
                <a-col>
                  <a-tooltip>
                    <template #title v-if="_service.name.length > 100">
                      <small class="text-uppercase">{{ _service.name }}</small>
                    </template>
                    <b class="text-uppercase text-700">{{ truncateString(_service.name, 100) }}</b>
                  </a-tooltip>
                </a-col>
                <a-col>
                  <a-tag color="#c63838">
                    {{ _service.code }}
                  </a-tag>
                </a-col>
              </a-row>
            </div>

            <div
              v-bind:class="[!props.flag_simulation ? (props.flag_preview ? 'px-2' : 'py-3') : '']"
            >
              <template v-if="!props.show_communication">
                <a-row type="flex" align="middle" justify="space-between" v-if="props.flag_preview">
                  <a-col class="d-flex">
                    <span>
                      <font-awesome-icon
                        :icon="
                          filesStore.showServiceIcon(
                            _service.search_parameters_services.category_id
                          )
                        "
                        style="font-size: 1.2rem"
                      />
                    </span>
                    <span class="mx-2 ellipsis d-block">
                      <b>{{ _service.name }}</b>
                    </span>
                    <a-tag color="#c63838" class="mx-2">
                      {{ _service.code }}
                    </a-tag>
                  </a-col>
                </a-row>
                <a-row type="flex" justify="space-between" align="middle">
                  <a-col>
                    <a-row type="flex" justify="start" align="middle" style="gap: 5px">
                      <a-col>
                        <CalendarOutlined />
                      </a-col>
                      <a-col>
                        <b>{{
                          formatDate(_service.search_parameters_services.date, 'DD/MM/YYYY')
                        }}</b>
                      </a-col>
                    </a-row>
                  </a-col>
                  <a-col>
                    <b>Pasajeros: </b>
                    {{ _service.quantity_adults }} <i class="bi bi-person-fill"></i>
                    {{ _service.quantity_child }} <i class="bi bi-person-arms-up"></i>
                  </a-col>
                  <a-col>
                    <a
                      href="javascript:;"
                      v-on:click="showInformation('to', _service)"
                      class="text-danger text-underline"
                      >Más información del servicio</a
                    >
                  </a-col>
                  <a-col>
                    <i class="bi bi-warning-triangle text-warning"></i>
                    <b>Costo:</b>
                    <b class="h5 text-danger mb-0 mx-1"
                      >$ {{ formatNumber({ number: _service.price }) }}</b
                    >
                  </a-col>
                </a-row>
              </template>

              <template v-if="props.show_communication">
                <template v-if="filesStore.isLoadingAsync || !search_communication">
                  <a-skeleton active />
                </template>
                <template v-else>
                  <template v-if="flag_communication_to">
                    <div v-if="!filesStore.isLoadingAsync && _service.communications.length > 0">
                      <p v-if="flag_simulation" class="mb-0">
                        <span>
                          <svg
                            width="18"
                            height="19"
                            viewBox="0 0 18 19"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                          >
                            <path
                              d="M13.5 16.373C14.7426 16.373 15.75 15.3657 15.75 14.123C15.75 12.8804 14.7426 11.873 13.5 11.873C12.2574 11.873 11.25 12.8804 11.25 14.123C11.25 15.3657 12.2574 16.373 13.5 16.373Z"
                              stroke="#212529"
                              stroke-width="2"
                              stroke-linecap="round"
                              stroke-linejoin="round"
                            />
                            <path
                              d="M4.5 7.37305C5.74264 7.37305 6.75 6.36569 6.75 5.12305C6.75 3.88041 5.74264 2.87305 4.5 2.87305C3.25736 2.87305 2.25 3.88041 2.25 5.12305C2.25 6.36569 3.25736 7.37305 4.5 7.37305Z"
                              stroke="#212529"
                              stroke-width="2"
                              stroke-linecap="round"
                              stroke-linejoin="round"
                            />
                            <path
                              d="M9.75 5.12305H12C12.3978 5.12305 12.7794 5.28108 13.0607 5.56239C13.342 5.84369 13.5 6.22522 13.5 6.62305V11.873"
                              stroke="#212529"
                              stroke-width="2"
                              stroke-linecap="round"
                              stroke-linejoin="round"
                            />
                            <path
                              d="M4.5 7.37305V16.373"
                              stroke="#212529"
                              stroke-width="2"
                              stroke-linecap="round"
                              stroke-linejoin="round"
                            />
                          </svg>
                        </span>
                        Servicios con comunicación de reserva a proveedores
                      </p>

                      <div
                        class="my-3 p-3"
                        style="border: 1px solid #e9e9e9; border-radius: 6px"
                        v-for="(communication, c) in _service.communications"
                      >
                        <a-row align="middle" justify="space-between" class="mb-3">
                          <a-col>
                            <a-row type="flex" style="gap: 5px">
                              <a-col>
                                <font-awesome-icon :icon="['fas', 'user-gear']" />
                              </a-col>
                              <a-col>
                                <b>
                                  <small class="text-uppercase">
                                    {{ communication.supplier_name }}
                                  </small>
                                </b>
                              </a-col>
                            </a-row>
                          </a-col>
                          <a-col
                            v-if="
                              communication.send_communication &&
                              communication?.supplier_emails?.length > 0
                            "
                          >
                            <!-- span>Correo asociado a reservas:</span -->
                            <span class="text-danger">
                              <a-tooltip>
                                <template #title>
                                  <small
                                    class="d-block"
                                    v-for="email in communication.supplier_emails"
                                  >
                                    {{ email }}
                                  </small>
                                </template>
                                <font-awesome-icon
                                  :icon="['fas', 'envelope-circle-check']"
                                  class="mx-2"
                                />
                              </a-tooltip>
                            </span>
                            <span
                              class="cursor-pointer"
                              v-on:click="communication.show_emails = !communication.show_emails"
                            >
                              <a-tooltip>
                                <template #title>Ver correos asignados</template>
                                <font-awesome-icon
                                  :icon="['far', 'square-plus']"
                                  class="text-danger"
                                />
                              </a-tooltip>
                            </span>

                            <modal-add-emails
                              v-if="communication.show_emails"
                              @close="communication.show_emails = !communication.show_emails"
                              @save="saveNote(communication, 'new')"
                              v-model="communication.supplier_emails"
                            />
                          </a-col>
                        </a-row>

                        <div
                          class="bg-purple-stick mt-3"
                          v-for="(component, cp) in communication.components"
                        >
                          <a-row type="flex" align="middle" justify="space-between">
                            <a-col>
                              <span class="d-block">
                                <a-tooltip>
                                  <template #title v-if="component.name.length > 35">
                                    <small class="text-uppercase">{{ component.name }}</small>
                                  </template>
                                  {{
                                    type === 'modification'
                                      ? truncateString(component.name, 35)
                                      : component.name
                                  }}
                                </a-tooltip>
                              </span>
                            </a-col>
                            <a-col>
                              {{ t('global.label.cost') }}:
                              <b
                                >${{
                                  formatNumber({ number: component.amount_cost, digits: 2 })
                                }}</b
                              >
                            </a-col>
                          </a-row>
                        </div>

                        <div class="my-3">
                          <a-row align="middle" type="flex" justify="space-between" class="mx-2">
                            <a-col>
                              <a-row
                                align="middle"
                                type="flex"
                                justify="start"
                                style="gap: 5px"
                                @click="communication.showNotesTo = !communication.showNotesTo"
                              >
                                <a-col>
                                  <div
                                    v-bind:class="[
                                      'd-flex cursor-pointer',
                                      !communication.showNotesTo ? 'text-dark-gray' : 'text-danger',
                                    ]"
                                  >
                                    <template v-if="communication.showNotesTo">
                                      <i
                                        class="bi bi-check-square-fill text-danger d-flex"
                                        style="font-size: 1.5rem"
                                      ></i>
                                    </template>
                                    <template v-else>
                                      <i class="bi bi-square d-flex" style="font-size: 1.5rem"></i>
                                    </template>
                                  </div>
                                </a-col>
                                <a-col>
                                  <span class="cursor-pointer">{{
                                    t('global.label.add_note_to_provider')
                                  }}</span>
                                </a-col>
                              </a-row>
                            </a-col>
                          </a-row>
                          <div class="mb-3 mx-2" v-if="communication.showNotesTo">
                            <template v-if="communication.lockedNotesTo">
                              <a-card
                                style="width: 100%"
                                class="mt-3"
                                :headStyle="{ background: black }"
                              >
                                <template #title
                                  >{{ t('global.label.note_in_reserve_to_provider') }}:</template
                                >
                                <template #extra>
                                  <a
                                    href="javascript:;"
                                    @click="communication.lockedNotesTo = false"
                                    class="text-danger"
                                  >
                                    <i class="bi bi-pencil"></i>
                                  </a>
                                </template>
                                <p class="mb-2">
                                  <b>{{ communication.notas }}</b>
                                </p>
                                <template v-for="(file, f) in communication.filesTo" :key="f">
                                  <a-row align="middle" class="mb-2">
                                    <i class="bi bi-paperclip"></i>
                                    <a :href="file" target="_blank" class="text-dark mx-1">
                                      {{ showName(file) }}
                                    </a>
                                  </a-row>
                                </template>
                              </a-card>
                            </template>

                            <template v-if="!communication.lockedNotesTo">
                              <p class="text-danger my-2">
                                {{ t('global.label.note_to_provider') }}:
                              </p>
                              <a-row align="top" justify="space-between">
                                <a-col flex="auto">
                                  <a-textarea
                                    v-model:value="communication.notas"
                                    :maxlength="100"
                                    show-count
                                    :placeholder="`${t('global.label.placeholder_note_to_provider')}`"
                                    :auto-size="{ minRows: 2 }"
                                  />
                                </a-col>
                                <a-col class="mx-2">
                                  <file-upload
                                    v-bind:folder="'communications'"
                                    @onResponseFiles="responseFilesTo(communication, $event)"
                                  />
                                </a-col>
                                <a-col>
                                  <a-button
                                    danger
                                    type="default"
                                    size="large"
                                    v-bind:disabled="
                                      !(
                                        communication.notas != '' ||
                                        communication?.filesTo.length > 0
                                      )
                                    "
                                    class="d-flex ant-row-middle text-600"
                                    @click="saveNote(communication, 'new')"
                                    :loading="
                                      filesStore.isLoadingNotes ||
                                      communicationsStore.isLoading ||
                                      communicationsStore.isLoadingAsync
                                    "
                                  >
                                    <i
                                      v-bind:class="[
                                        'bi bi-floppy',
                                        filesStore.isLoadingNotes ||
                                        communicationsStore.isLoading ||
                                        communicationsStore.isLoadingAsync
                                          ? 'mx-2'
                                          : '',
                                      ]"
                                    ></i>
                                  </a-button>
                                </a-col>
                              </a-row>
                            </template>
                          </div>
                          <template
                            v-if="communication.lockedNotesTo || !communication.showNotesTo"
                          >
                            <a-row align="middle" type="flex" justify="end" class="mx-2">
                              <a-col class="ant-row-end">
                                <a-button
                                  danger
                                  type="default"
                                  class="d-flex ant-row-middle text-600"
                                  @click="showCommunicationTo(communication)"
                                  :loading="
                                    communicationsStore.isLoading ||
                                    communicationsStore.isLoadingAsync
                                  "
                                >
                                  <i
                                    class="bi bi-search"
                                    v-if="
                                      !(
                                        communicationsStore.isLoading ||
                                        communicationsStore.isLoadingAsync
                                      )
                                    "
                                  ></i>
                                  <span class="mx-2">{{
                                    t('global.label.show_communication')
                                  }}</span>
                                </a-button>
                              </a-col>
                            </a-row>
                          </template>
                        </div>
                      </div>
                    </div>
                  </template>
                  <template v-else>
                    <template v-if="search_communication">
                      <a-empty :description="null" class="py-3 bg-light m-0" />
                    </template>
                  </template>
                </template>
              </template>
            </div>
          </div>
        </a-col>
      </template>
    </a-row>
  </div>

  <div class="my-3" v-if="props.buttons">
    <a-row type="flex" justify="end" align="middle">
      <a-col>
        <a-button
          type="default"
          class="mx-2 px-4 text-600"
          v-on:click="prevStep()"
          default
          :disabled="filesStore.isLoading || filesStore.isLoadingAsync"
          size="large"
        >
          {{ t('global.button.cancel') }}
        </a-button>
        <a-button
          type="primary"
          class="mx-2 px-4 text-600"
          v-on:click="processReservation(false)"
          default
          :disabled="filesStore.isLoading || filesStore.isLoadingAsync"
          size="large"
        >
          <template v-if="type == 'cancellation'">{{ t('global.button.continue') }}</template>
          <template v-else>{{ t('global.button.reserve') }}</template>
        </a-button>
      </a-col>
    </a-row>
  </div>

  <a-modal v-model:open="modal.open" :width="800" :closable="false" :maskClosable="false">
    <template #footer>
      <a-row align="middle" justify="center">
        <a-col>
          <a-button
            key="button"
            type="primary"
            default
            size="large"
            class="text-600"
            @click="closeModal"
            >{{ t('global.button.close') }}
          </a-button>
        </a-col>
      </a-row>
    </template>
    <IframeHTML :html="modal.html" />
  </a-modal>

  <a-modal v-model:visible="modalInformation" :width="800">
    <template #title>
      <div class="text-left px-4 pt-4">
        <h6 class="mb-0">{{ service.name }}</h6>
        <a-tag
          color="#EB5757"
          style="
            position: absolute;
            top: 0px;
            right: 120px;
            border-top-left-radius: 0;
            border-top-right-radius: 0;
            padding: 7px 15px;
            font-size: 18px;
            font-weight: 500;
          "
        >
          {{ service.service_type.name }}
        </a-tag>
      </div>
    </template>
    <div class="px-2">
      <a-row :gutter="24" type="flex" justify="space-between" align="top">
        <a-col :span="14">
          <p class="text-700">Operatividad</p>
          <p class="mb-0">Sistema horario de 24 horas</p>
          <p>
            {{ service.operations.turns[0].departure_time }}
            {{ service.operations.turns[0].shifts_available }}
          </p>
        </a-col>
        <a-col :span="10">
          <template v-if="service.inclusions.length > 0">
            <p>
              <b>Incluye</b>
            </p>
            <p>
              <template v-for="inclusion in service.inclusions">
                <a-tooltip v-for="item in inclusion.include">
                  <template v-if="item.name.length > 50" #title>{{ item.name }}</template>
                  <a-tag class="mb-2">{{ truncateString(item.name, 50) }}</a-tag>
                </a-tooltip>
              </template>
            </p>
          </template>
          <p>
            <b>Disponibilidad</b>
          </p>
          <a-row type="flex" justify="space-between" align="top" style="gap: 5px">
            <a-col>
              <svg
                width="24"
                height="25"
                viewBox="0 0 24 25"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M22 11.2049V12.1249C21.9988 14.2813 21.3005 16.3795 20.0093 18.1067C18.7182 19.8338 16.9033 21.0973 14.8354 21.7088C12.7674 22.3202 10.5573 22.2468 8.53447 21.4994C6.51168 20.7521 4.78465 19.371 3.61096 17.5619C2.43727 15.7529 1.87979 13.6129 2.02168 11.4612C2.16356 9.30943 2.99721 7.26119 4.39828 5.62194C5.79935 3.98268 7.69279 2.84025 9.79619 2.36501C11.8996 1.88977 14.1003 2.1072 16.07 2.98486"
                  stroke="#1ED790"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
                <path
                  d="M22 4.125L12 14.135L9 11.135"
                  stroke="#1ED790"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
              </svg>
            </a-col>
            <a-col>
              <p class="mb-1">Dias:</p>
              <template v-if="Object.values(service.operations.days).length == 7">
                Todos los días
              </template>
              <template v-else>
                <p class="m-0" v-for="(day, d) in service.operations.days">
                  {{ d }}
                </p>
              </template>
            </a-col>
            <a-col>
              <p class="mb-1">Horario</p>
              <template v-if="Object.values(service.operations.days).length == 7">
                <p class="text-danger text-400 mb-0">
                  {{ service.operations.schedule[0]['friday'] }}.
                </p>
              </template>
              <template v-else>
                <p class="m-0" v-for="(day, d) in service.operations.schedule">{{ d }}.</p>
              </template>
            </a-col>
          </a-row>
        </a-col>
      </a-row>
    </div>
    <template #footer></template>
  </a-modal>

  <a-modal
    v-if="props.buttons"
    v-model:open="modalCommunication"
    :width="450"
    :closable="false"
    :maskClosable="false"
  >
    <template #title>
      <span class="text-center">{{ t('files.label.communication_to_provider') }}</span>
    </template>

    <a-alert type="info" class="mb-0">
      <template #message>
        <a-row type="flex" style="gap: 15px; flex-flow: nowrap !important" align="middle">
          <a-col>
            <font-awesome-icon :icon="['fas', 'circle-info']" size="xl" />
          </a-col>
          <a-col flex="auto">
            {{ t('files.message.no_communication_service') }}
          </a-col>
        </a-row>
      </template>
    </a-alert>

    <template #footer>
      <a-row align="middle" justify="center">
        <a-col>
          <a-button
            key="button"
            type="default"
            default
            size="large"
            class="text-600"
            :disabled="filesStore.isLoading || filesStore.isLoadingAsync"
            v-on:click="prevStep()"
            >{{ t('global.button.cancel') }}</a-button
          >
          <a-button
            key="button"
            type="primary"
            default
            size="large"
            class="text-600"
            :disabled="filesStore.isLoading || filesStore.isLoadingAsync"
            v-on:click="processReservation(false)"
            >{{ t('global.button.continue') }}</a-button
          >
        </a-col>
      </a-row>
    </template>
  </a-modal>

  <a-modal v-model:open="open" width="80%">
    <a-row class="mt-2">
      <a-col :span="24" style="display: flex; justify-content: flex-start; align-items: center">
        <svg
          width="24"
          height="24"
          viewBox="0 0 24 24"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
          class="mr-2"
        >
          <g clip-path="url(#clip0_25805_10854)">
            <path
              d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z"
              stroke="#FF3B3B"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
            <path
              d="M12 8V12"
              stroke="#FF3B3B"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
            <path
              d="M12 16H12.01"
              stroke="#FF3B3B"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </g>
          <defs>
            <clipPath id="clip0_25805_10854">
              <rect width="24" height="24" fill="white" />
            </clipPath>
          </defs>
        </svg>
        <div class="ms-2">{{ message_modal }}</div>
      </a-col>
    </a-row>
    <a-row class="mt-2">
      <a-col :span="24">
        <a-table
          :dataSource="master_services"
          :columns="columns"
          :rowClassName="setRowClass"
          :pagination="false"
          rowKey="code"
        >
          <template #bodyCell="{ column, record }">
            <template v-if="column.key === 'components'">
              <small
                v-if="record.components.length > 0"
                v-html="
                  record.components
                    .flatMap((component) => component.response.message)
                    .join('<br />')
                "
              >
              </small>
            </template>

            <template v-else-if="column.key === 'critical'">
              <a-tag :color="record.response.critical ? 'red' : 'green'">
                {{ record.response.critical ? 'Crítico' : 'Normal' }}
              </a-tag>
            </template>

            <template v-else-if="column.key === 'message'">
              <span v-if="record.response.error" style="color: #ff4d4f">
                {{ record.response.message }}
              </span>
              <span v-else style="color: #52c41a"> Sin errores </span>
            </template>
          </template>
        </a-table>
      </a-col>
      <!-- <a-col :spin="24">{{ message_modal }}</a-col> -->
    </a-row>

    <template #footer>
      <!-- Solo incluyes los botones que quieras mostrar -->
      <a-button type="primary" v-if="!type_critical" @click="handleProcess">{{
        t('global.button.continue')
      }}</a-button>
      <a-button type="primary" v-else @click="handleOk">{{ t('global.button.close') }}</a-button>
    </template>
  </a-modal>
</template>

<script setup>
  import { onBeforeMount, ref, watch } from 'vue';
  import dayjs from 'dayjs';
  import { formatDate, formatNumber, truncateString } from '@/utils/files.js';
  import { CalendarOutlined } from '@ant-design/icons-vue';
  import { useFilesStore } from '@store/files';
  import { useCommunicationsStore } from '@/stores/global';
  import FileUpload from '@/components/global/FileUploadComponent.vue';
  import ModalAddEmails from '@/components/files/reusables/ModalAddEmails.vue';
  import Cookies from 'js-cookie';
  import IconUserConfiguration from '@/components/icons/IconUserConfiguration.vue';
  import IframeHTML from './IframeHTML.vue';
  import { notification } from 'ant-design-vue';

  import { useI18n } from 'vue-i18n';
  const { t } = useI18n({
    useScope: 'global',
  });

  const emit = defineEmits(['onPrevStep', 'onNextStep', 'onLoadReservation', 'onSaveNote']);

  const params_cancellation = ref({});
  const communicationsCancellation = ref([]);

  const props = defineProps({
    buttons: {
      type: Boolean,
      default: () => true,
    },
    show_communication: {
      type: Boolean,
      default: () => true,
    },
    title: {
      type: Boolean,
      default: () => true,
    },
    itinerary: {
      type: Object,
      default: () => {},
    },
    from: {
      type: Object,
      default: () => {},
    },
    to: {
      type: Object,
      default: () => {},
    },
    selected: {
      type: Array,
      default: () => [],
    },
    date_from: {
      type: String,
      default: () => '',
    },
    date_to: {
      type: String,
      default: () => '',
    },
    type: {
      type: String,
      default: () => '',
    },
    filter: {
      type: Object,
      default: () => {},
    },
    flag_simulation: {
      type: Boolean,
      default: () => false,
    },
    flag_preview: {
      type: Boolean,
      default: () => false,
    },
    flag_notes: {
      type: Boolean,
      default: () => false,
    },
    status_reason_name: {
      type: String,
      default: () => '',
    },
  });

  const flag_communication_from = ref(false);
  const flag_communication_to = ref(false);
  const search_communication = ref(false);

  const saveNote = async (communication, type) => {
    if (type == 'cancellation') {
      const params = {
        code_request_book: communication.code_request_book,
        supplier_name: communication.supplier_name,
        supplier_emails: communication.supplier_emails,
        send_communication: communication.send_communication,
        html: '',
        components: communication.components,
        total_penality: communication.penality,
        motive: props.flag_notes ? props.status_reason_name : '',
        notas: communication.notas,
      };

      const { cancellation } = await filesStore.updateServiceNotes({
        type: 'cancellation',
        fileId: filesStore.getFile.id,
        itineraryId: filesStore.getFileItinerary.id,
        params: {
          cancellations: [params],
        },
      });

      communication.html = cancellation[0].html;
      communication.lockedNotesFrom = true;
    }

    if (type == 'new') {
      const amount_cost = communication.components.reduce(
        (total, component) => total + parseFloat(component.amount_cost),
        0
      );

      const params = {
        code_request_book: communication.code_request_book,
        supplier_name: communication.supplier_name,
        supplier_emails: communication.supplier_emails,
        send_communication: communication.send_communication,
        html: '',
        components: communication.components,
        total_penality: communication.penality,
        motive: props.flag_notes ? props.status_reason_name : '',
        notas: communication.notas,
        total_amount_cost: amount_cost,
      };

      const { reservations } = await filesStore.updateServiceNotes({
        type: 'news',
        fileId: filesStore.getFile.id,
        fileItineraryId: 0,
        params: {
          reservations: [params],
        },
      });

      communication.html = reservations[0].html;
      communication.lockedNotesTo = true;
    }
  };

  function sleep(ms) {
    return new Promise((resolve) => setTimeout(resolve, ms));
  }

  onBeforeMount(async () => {
    client_id.value = localStorage.getItem('client_id') || 15766;

    if (props.buttons) {
      if (props.type === 'new') {
        await handleNewType();
      } else if (props.type === 'modification') {
        await handleModificationType();
      } else if (props.type === 'cancellation') {
        await handleCancellationType();
      }
    }

    if (props.from) {
      const services_cancellation = props.from.services.map((service) => ({
        id: service.id,
        compositions: service.compositions.map((c) => c.id),
      }));
      params_cancellation.value = services_cancellation;
    }
  });

  const fetchAndAssignCommunications = async (service, index, params_cancellation = {}) => {
    const params = {
      code: service.code,
      date_in: dayjs(service.date_from).format('DD/MM/YYYY'),
      total_passengers: service.quantity_adults,
      total_children: service.quantity_child,
      // start_time: service.start_time,
    };

    await filesStore.fetchMasterServices([params]);
    const master_services = filesStore.getMasterServicesCommunications ?? [];

    if (master_services.length > 0) {
      let params_communication = null;
      let fileItineraryId = null;

      const masterServices = master_services[0]?.master_services || [];

      switch (type_to.value) {
        case 'new':
          params_communication = {
            type: 'new',
            services: {
              delete: [],
              new: masterServices,
              update: [],
            },
          };
          fileItineraryId = '';
          break;

        case '':
          const updateParams = props.from.services.map((service, index) => ({
            service_id: service.id,
            service_change: masterServices[index],
          }));

          fileItineraryId = `${props.from.id}`;
          params_communication = {
            type: 'update',
            itinerary_id: fileItineraryId,
            services: {
              delete: [],
              new: [],
              update: updateParams,
            },
          };
          break;

        default:
          // Puedes manejar otros casos aquí si es necesario
          break;
      }

      // service.communications = [];

      if (index === 0) {
        service.params_cancellation = params_cancellation;
      }

      if (params_communication) {
        await filesStore.fetchCommunicationsNew(
          type_to.value,
          filesStore.getFile.id,
          fileItineraryId,
          params_communication
        );

        let items = [];

        if (type_to.value === 'new') {
          items = filesStore.getCommunications?.reservations || [];
        }

        if (type_to.value === '') {
          items = filesStore.getCommunications?.modification || [];
        }

        const communications = items.map((communication) => ({
          ...communication,
          filesTo: [],
          filesFrom: [],
          lockedNotesTo: false,
          lockedNotesFrom: false,
        }));
        service.communications = communications;
        service.params_communication = params_communication;
      }

      if (service.communications.length > 0) {
        flag_communication_to.value = true;
      }
    } else {
      flag_communication_to.value = false;
    }
  };

  const handleNewType = async () => {
    type_to.value = 'new';

    await Promise.all(
      props.to.map(async (service, s) => {
        service.reservation_time = null;
        await fetchAndAssignCommunications(service, s);
      })
    );

    search_communication.value = true;
  };

  const handleModificationType = async () => {
    const isModification = false; // props.from.object_code === props.to[0].code;

    if (!isModification) {
      type_from.value = 'cancellation';
      type_to.value = 'new';

      if (props.show_communication) {
        await Promise.all(
          props.to.map(async (service, s) => {
            service.reservation_time = null;
            await fetchAndAssignCommunications(service, s);
          })
        );

        const params = {
          services: {
            delete: props.from.services.map((s) => s.id),
            new: [],
            update: [],
          },
        };

        await filesStore.fetchCommunicationsCancellation(
          filesStore.getFile.id,
          props.from.id,
          params
        );

        communicationsCancellation.value = (filesStore.getCommunications.cancellation || []).map(
          (cancellation) => {
            return {
              ...cancellation,
              filesFrom: [],
              notas: '',
            };
          }
        );

        if (communicationsCancellation.value.length > 0) {
          flag_communication_from.value = true;
        }
      }

      search_communication.value = true;
    } else {
      type_from.value = 'modification';
      type_to.value = '';

      const params_cancellation = {
        services: {
          delete: props.from.services.map((s) => s.id),
          new: [],
          update: [],
        },
      };

      if (props.show_communication) {
        await Promise.all(
          props.to.map(async (service, s) => {
            service.reservation_time = null;
            await fetchAndAssignCommunications(service, s, params_cancellation);
          })
        );
      }

      search_communication.value = true;
    }
  };

  const flag_hidden = ref(false);

  const handleCancellationType = async (hidden = false) => {
    flag_hidden.value = hidden;
    type_from.value = 'cancellation';
    type_to.value = '';

    if (props.show_communication) {
      const params = {
        services: {
          delete: props.from.services.map((s) => s.id),
          new: [],
          update: [],
        },
      };

      if (!props.buttons) {
        await sleep(1500);
      }

      await filesStore.fetchCommunicationsCancellation(
        filesStore.getFile.id,
        props.from.id,
        params
      );

      communicationsCancellation.value = (filesStore.getCommunications.cancellation || []).map(
        (cancellation) => {
          return {
            ...cancellation,
            filesFrom: [],
            notas: '',
          };
        }
      );

      if (communicationsCancellation.value.length > 0) {
        flag_communication_from.value = true;
      }
    }

    search_communication.value = true;
  };

  const filesStore = useFilesStore();
  const communicationsStore = useCommunicationsStore();

  // const filter = ref({});
  const type_from = ref('');
  const type_to = ref('');

  const client_id = ref('');

  const modal = ref({
    open: false,
    html: '',
    subject: '',
  });

  const showCommunicationFrom = async (communication) => {
    modal.value.html = communication.html;
    modal.value.subject = communication.supplier_name;
    modal.value.open = true;
  };

  const showCommunicationTo = async (communication) => {
    modal.value.html = communication.html;
    modal.value.subject = communication.supplier_name;
    modal.value.open = true;
  };

  const closeModal = () => {
    modal.value.open = false;
  };

  const responseFilesFrom = (communication, files) => {
    communication.filesFrom = files.map((item) => item.link);
  };

  const responseFilesTo = (communication, files) => {
    communication.filesTo = files.map((item) => item.link);
  };

  const showName = (file) => {
    let parts = file.split('/').splice(-1);
    return parts[0];
  };

  const open = ref(false);
  const message_modal = ref('');
  const type_critical = ref(true);

  const handleOk = () => {
    open.value = false;
  };

  const master_services = ref([]);

  const columns = [
    {
      title: 'Código',
      dataIndex: 'code',
      key: 'code',
    },
    {
      title: 'Tipo IFX',
      dataIndex: 'type_ifx',
      key: 'type_ifx',
    },
    {
      title: 'Fecha',
      dataIndex: 'date_in',
      key: 'date_in',
    },
    {
      title: 'Hora',
      dataIndex: 'start_time',
      key: 'start_time',
    },
    {
      title: 'Penalización',
      dataIndex: 'penalty',
      key: 'penalty',
    },
    {
      title: 'Componentes',
      key: 'components',
    },
    {
      title: 'Estado',
      key: 'critical',
    },
    {
      title: 'Mensaje',
      key: 'message',
    },
  ];

  const setRowClass = (record) => {
    return record.response.critical ? 'critical-row' : '';
  };

  const processReservation = async (flag_return) => {
    let cancellation = {};
    let response = {};

    modalCommunication.value = false;

    if (props.type != 'new') {
      /*
      const services = props.from.services.map((service) => {
        return {
          id: service.id,
          compositions: service.compositions.map((composition) => composition.id),
        };
      });

      const responseValidate = await filesStore.handleValidateItinerary({
        services: services,
        file_itinerary_id: props.from.id,
      });

      if (!responseValidate.success) {
        const masterService = responseValidate?.data?.error?.master_services;

        if (!masterService) {
          message_modal.value =
            responseValidate?.data?.error?.message ||
            responseValidate?.message ||
            'Error interno del servidor.';
          notification.error({
            message: 'Error',
            description: message_modal.value,
          });
        } else {
          open.value = true;
          message_modal.value =
            responseValidate?.data?.error?.message || 'Error interno del servidor.';
          master_services.value = masterService;
          type_critical.value =
            masterService.length > 0
              ? masterService.some((service) => service.response?.critical)
              : true;
        }

        return false;
      }
      */

      if (filesStore.getError !== '') {
        notification.error({
          message: 'Error',
          description: filesStore.getError,
        });
        return false;
      }

      const params_communication = {
        services: {
          delete: props.from.services.map((s) => s.id),
          new: [],
          update: [],
        },
      };

      cancellation = {
        type: 'service',
        flag_email: 'cancellation',
        file_id: filesStore.getFile.id,
        file_number: filesStore.getFile.fileNumber,
        itinerary_id: props.from.id,
        code: props.from.object_code,
        services: params_cancellation.value,
        notas: service.notas,
        attachments: service.filesFrom,
        params_communication: params_communication,
      };

      response = {
        type: 'cancellation',
        reservation: props,
        params: cancellation,
      };

      if (typeof flag_return == 'undefined' || !flag_return) {
        emit('onLoadReservation', response);
      }
    }

    if (props.type !== 'cancellation') {
      let reservationServices = [];

      for (const service of props.to) {
        service?.communications?.forEach((communication) => {
          communication.html = '';
        });

        service.rate.political = [];
        reservationServices.push(service);
      }

      const originalPassengers = filesStore.getFilePassengers;
      const passengers = originalPassengers.map((p) => ({
        ...p,
        states: null,
        date_birth:
          p.date_birth && p.date_birth !== '0000-00-00'
            ? dayjs(p.date_birth, 'DD/MM/YYYY').format('YYYY-MM-DD')
            : '',
      }));

      let reservation = {
        client_id: localStorage.getItem('client_id'),
        executive_id: Cookies.get(window.USER_ID),
        file_code: filesStore.getFile.fileNumber,
        reference: '',
        guests: passengers,
        reservations: [],
        reservations_services: reservationServices,
        entity: 'Cart',
        object_id: null,
      };

      let params = {
        type: 'service',
        flag_email: 'new',
        reservation_add: reservation,
        notas: '',
        attachments: [],
        file_id: filesStore.getFile.id,
      };

      response = {
        type: 'new',
        reservation: props,
        params: params,
      };

      if (typeof flag_return === 'undefined' || !flag_return) {
        emit('onLoadReservation', response);
      }
    }

    if (flag_return) {
      return response;
    }
  };

  const prevStep = () => {
    emit('onPrevStep');
  };

  const modalCommunication = ref(false);

  watch(
    [search_communication],
    ([newValor1]) => {
      if (newValor1) {
        if (!flag_communication_from.value && !flag_communication_to.value) {
          modalCommunication.value = true;
          /*
          setTimeout(async () => {
            await processReservation(false);
          }, 100);
          */
        }
      }
    },
    { immediate: true }
  );

  const service = ref({});
  const modalInformation = ref(false);

  const showInformation = async (type, _service) => {
    if (type === 'from') {
      await filesStore.findServiceInformation(
        _service.object_id,
        _service.date_in,
        _service.adults + _service.children
      );
    }

    if (type === 'to') {
      await filesStore.findServiceInformation(
        _service.service_id,
        _service.date_from,
        _service.quantity_adults + _service.quantity_children
      );
    }

    service.value = filesStore.getServiceInformation;

    setTimeout(() => {
      modalInformation.value = true;
    }, 100);
  };

  defineExpose({
    processReservation,
    handleCancellationType,
  });
</script>
