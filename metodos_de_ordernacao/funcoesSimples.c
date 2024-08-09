#include <stdio.h>
#include <stdlib.h>

#include "metodos_de_ordenacao.h"

void geraValoresCrescente(int *vet, int tam) {

    for (int i = 0; i < tam; i++)
        vet[i] = i;
}

void geraValoresDecrescente(int *vet, int tam) {

    for (int i = tam; i > 0; i--)
        vet[i] = i;
}

void geraValoresAleatorios(int *vet, int tam) {

    for (int i = 0; i < tam; i++)
        vet[i] = rand()%(10*tam);
}

void fazTroca(int* a, int* b, int* trocas) {

    int aux = *a;
    *a = *b;
    *b = aux;
    (*trocas)++;
}

void printVetor(int *vet, int tam) {

    for (int i = 0; i < tam; i++)
        printf("%d ", vet[i]);
    printf("\n");
}