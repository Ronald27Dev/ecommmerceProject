#include <stdio.h>
#include <stdlib.h>
#include <math.h>

#include "metodos_de_ordenacao.h"

//-------------------------------------------SELECTION SORT--------------------------------------------------\\

void selectionSort(int *vet, int tam, int *trocas) {

    for (int i = 0; i < tam - 1; i++) {
        for (int j = i + 1; j < tam; j++)
            if(vet[i] > vet[j]) fazTroca(&vet[i], &vet[j], trocas);
    }
}

//--------------------------------------------INSERTION SORT--------------------------------------------------\\

void insertionSort(int *vet, int tam, int *trocas) {

    for (int i = 0; i < tam - 1; i++){
        
        if(vet[i] > vet[i+1]){

            int aux = vet[i+1];
            vet[i+1] = vet[i];
            vet[i] = aux;
            int j = i - 1;
            (*trocas)++;

            while(j >= 0){

                if(aux < vet[j]) {

                    vet[j+1] = vet[j];
                    vet[j] = aux;
                    (*trocas)++;
                } else break;

                j = j - 1;
            }
        }
    }
}

//-----------------------------------------------BUBBLE SORT------------------------------------------------\\

void bubbleSort(int *vet, int tam, int *trocas) {

    for (int i = 0; i < tam; i++){
        for (int j = 0; j < tam - 1 - i; j++)
            if (vet[i] > vet[j]) fazTroca(&vet[i], &vet[j], trocas);
    }    
}

//-------------------------------------------------SHELL SORT------------------------------------------------\\

void shellSort(int *vet, int tam, int *trocas) {

    int h = 1;

    while (h < tam)
        h = h * 3 + 1;
    
    while (h > 1) {
        h /= 3;

        for (int i = h; i < tam; i++) {
            
            int aux = vet[i];
            int j = i - h;

            while(j >= 0 && aux < vet[j]){

                vet[j+h] = vet[j];

                j-= h;

                (*trocas)++;
            }
            vet[j+h] = aux;
        }
    }
}

//-------------------------------------------------MERGE SORT-------------------------------------------------\\

void merge(int *vetorPrincipal, int inicio, int meio, int fim, int *trocas){

    int i, j, k;
    int tamE = meio  - inicio + 1;
    int tamD = fim - meio;

    int esquerda[tamE], direita[tamD];

    for(i = 0; i < tamE; i++)
        esquerda[i] = vetorPrincipal[inicio + i];
    for(j = 0; j < tamD; j++)
        direita[j] = vetorPrincipal[meio + 1 + j];

    i = 0;
    j = 0;
    k = inicio;

    while(i < tamE && j < tamD){
        
        if(esquerda[i] <= direita[j]){
            
            vetorPrincipal[k] = esquerda[i];
            i++;
            (*trocas)++;
        } else {
            
            vetorPrincipal[k] = direita[j];
            j++;
            (*trocas)++;
        }
        k++;
    }
    while (i < tamE){
        
        vetorPrincipal[k] = esquerda[i];
        i++;
        k++;
    }
    while (j< tamD){

        vetorPrincipal[k] = direita[j];
        j++;
        k++;    
    }
}

void mergeSort(int *vet, int inicio, int fim, int *trocas){

    if(inicio < fim){

        int meio = inicio + (fim - inicio) / 2;

        mergeSort(vet, inicio, meio, trocas);
        mergeSort(vet, meio + 1, fim, trocas);

        merge(vet, inicio, meio, fim ,trocas);
    }
}

//------------------------------------------------HEAP SORT--------------------------------------------------\\

void criaHeap(int *vet, int n, int i, int *trocas){

    int pai = i;
    int esq = 2 * i + 1;
    int dir = 2 * i +2;
    int aux;

    if(esq < n && vet[esq] > vet[pai]) pai = esq;
    if(dir < n && vet[dir] > vet[pai]) pai = dir;
    
    if(pai != n){
        fazTroca(&vet[i], &vet[pai], trocas);
        criaHeap(vet, n, pai, trocas);
    }
}

void heapSort(int *vet, int n, int *trocas){

    for(int i = (n / 2) - 1; i>= 0; i--) criaHeap(vet, n, i, trocas);

    for(int i = n - 1; i > 0; i--){

        int aux;
        aux = vet[0];
        vet[0] = vet[i];
        vet[i] = aux;

        criaHeap(vet, i, 0, trocas);
    }
}

//-----------------------------------------------RADIX SORT--------------------------------------------------\\

void radixSort(int *vet, int tam, int *trocas) {
    int max_value = vet[0];
    
    for (int i = 1; i < tam; i++) {
        if (vet[i] > max_value) 
            max_value = vet[i];
    }
    
    int num_digits = 0;
    
    while (max_value > 0) {
        max_value /= 10;
        num_digits++;
    }
    
    int buckets[10][tam];
    int bucket_sizes[10] = {0};
    
    for (int digit = 0; digit < num_digits; digit++) {
        // Reset bucket sizes
        for (int i = 0; i < 10; i++) 
            bucket_sizes[i] = 0;
        
        for (int i = 0; i < tam; i++) {
            int digit_value = (vet[i] / (int)pow(10, digit)) % 10;
            buckets[digit_value][bucket_sizes[digit_value]] = vet[i];
            bucket_sizes[digit_value]++;
        }
        
        int index = 0;
        for (int i = 0; i < 10; i++) {
            for (int j = 0; j < bucket_sizes[i]; j++) {
                if (vet[index] != buckets[i][j]) {
                    vet[index] = buckets[i][j];
                    (*trocas)++;
                }
                index++;
            }
        }
    }
}

//-----------------------------------------------QUICK SORT--------------------------------------------------\\

int particionar(int *vet, int inicio, int fim, int* trocas) {

    int pivot = vet[fim];
    int i = (inicio - 1);

    for (int j = inicio; j <= fim - 1; j++) {
        
        if (vet[j] <= pivot) {
            i++;
            fazTroca(&vet[i], &vet[j], trocas);
        }
    }
    fazTroca(&vet[i + 1], &vet[fim], trocas);
    return (i + 1);
}

void quickSort(int *vet, int inicio, int fim, int* trocas) {

    if (inicio < fim) {
        int pivot = particionar(vet, inicio, fim, trocas);

        quickSort(vet, inicio, pivot - 1, trocas);
        quickSort(vet, pivot + 1, fim, trocas);
    }
}

//-----------------------------------------------------------------------------------------------------------\\