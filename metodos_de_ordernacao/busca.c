#include <stdio.h>

int buscaSequencial(int vetor[], int tamanho, int chave) {
    for (int i = 0; i < tamanho; i++) {
        if (vetor[i] == chave) {
            return i;
        }
    }
    return -1; 
}

int buscaBinaria(int vetor[], int tamanho, int chave) {
    int inicio = 0;
    int fim = tamanho - 1;
    while (inicio <= fim) {
        int meio = (inicio + fim) / 2;
        if (vetor[meio] == chave) {
            return meio; 
        } else if (vetor[meio] < chave) {
            inicio = meio + 1;
        } else {
            fim = meio - 1;
        }
    }
    return -1;
}

int main() {
    int tamanho;
    printf("Digite o tamanho do vetor: ");
    scanf("%d", &tamanho);

    int vetor[tamanho];
    printf("Digite os elementos do vetor:\n");
    for (int i = 0; i < tamanho; i++) {
        scanf("%d", &vetor[i]);
    }

    int chave;
    printf("Digite o número a ser buscado: ");
    scanf("%d", &chave);

    int posicaoSequencial = buscaSequencial(vetor, tamanho, chave);
    if (posicaoSequencial != -1) {
        printf("Busca Sequencial: %d encontrado na posicao %d\n", chave, posicaoSequencial);
    } else {
        printf("Busca Sequencial: %d não encontrado no vetor\n", chave);
    }

    int posicaoBinaria = buscaBinaria(vetor, tamanho, chave);
    if (posicaoBinaria != -1) {
        printf("Busca Binaria: %d encontrado na posicao %d\n", chave, posicaoBinaria);
    } else {
        printf("Busca Binaria: %d não encontrado no vetor\n", chave);
    }

    return 0;
}